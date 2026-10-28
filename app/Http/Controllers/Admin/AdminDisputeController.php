<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\DisputeStatus;
use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Dispute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDisputeController extends Controller
{
    /**
     * Display list of disputes within regional boundary (Policy 11.1).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $disputes = Dispute::forAdminRegion($user)
            ->with(['booking.listing', 'customer', 'vendorProfile'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->boolean('escalated_only'), fn ($q) => $q->where('is_escalated', true))
            ->latest()
            ->paginate(15);

        return view('pages.admin.disputes.index', [
            'disputes' => $disputes,
            'user' => $user,
            'assignedRegion' => $user->assignedRegion,
        ]);
    }

    /**
     * Show dispute case worksheet with evidence, chat/logs, and arbitration tools (Policy 11.2).
     */
    public function show(Dispute $dispute): View
    {
        $this->authorizeAdminRegionalAccess($dispute);

        $dispute->load([
            'booking.listing.operationalRegion',
            'booking.vendorSettlement',
            'customer',
            'vendorProfile.user',
            'resolvedBy',
        ]);

        return view('pages.admin.disputes.show', [
            'dispute' => $dispute,
            'booking' => $dispute->booking,
            'vendorProfile' => $dispute->vendorProfile,
            'isSuperadmin' => auth()->user()->isSuperadmin(),
        ]);
    }

    /**
     * Resolve ordinary dispute (Policy 11.2).
     */
    public function resolve(Request $request, Dispute $dispute): RedirectResponse
    {
        $this->authorizeAdminRegionalAccess($dispute);

        if ($dispute->is_escalated && ! auth()->user()->isSuperadmin()) {
            abort(403, 'Akses Ditolak: Kasus kritis ini wajib diselesaikan oleh Superadmin (Policy 11.1).');
        }

        $request->validate([
            'resolution_notes' => ['required', 'string', 'min:5', 'max:1000'],
            'compensation_amount' => ['nullable', 'numeric', 'min:0'],
            'vendor_penalty_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $comp = (float) $request->input('compensation_amount', 0);
        $penalty = (float) $request->input('vendor_penalty_amount', 0);

        DB::transaction(function () use ($dispute, $comp, $penalty, $request) {
            $dispute->update([
                'status' => DisputeStatus::Resolved,
                'resolution_notes' => $request->input('resolution_notes'),
                'compensation_amount' => $comp,
                'vendor_penalty_amount' => $penalty,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            // Release escrow hold and adjust payout if penalty applied
            $settlement = $dispute->booking->vendorSettlement;
            if ($settlement && $settlement->status === SettlementStatus::OnHold) {
                $newPayout = max(0, $settlement->amount - $penalty);
                $settlement->update([
                    'amount' => $newPayout,
                    'status' => SettlementStatus::Pending,
                ]);
            }

            // Mark booking completed
            $dispute->booking->update(['status' => BookingStatus::Completed]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_resolved_dispute',
                'auditable_type' => Dispute::class,
                'auditable_id' => $dispute->id,
                'new_values' => [
                    'status' => 'resolved',
                    'compensation' => $comp,
                    'penalty' => $penalty,
                    'notes' => $request->input('resolution_notes'),
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.disputes.show', $dispute->id)
            ->with('success', 'Sengketa berhasil diselesaikan! Dana penahanan payout telah disesuaikan dan booking berstatus Selesai.');
    }

    /**
     * Reject dispute claim (Policy 11.2).
     */
    public function reject(Request $request, Dispute $dispute): RedirectResponse
    {
        $this->authorizeAdminRegionalAccess($dispute);

        if ($dispute->is_escalated && ! auth()->user()->isSuperadmin()) {
            abort(403, 'Akses Ditolak: Kasus kritis ini wajib diputuskan oleh Superadmin.');
        }

        $request->validate([
            'rejection_notes' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () use ($dispute, $request) {
            $dispute->update([
                'status' => DisputeStatus::Rejected,
                'resolution_notes' => 'Klaim ditolak: '.$request->input('rejection_notes'),
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            // Restore settlement hold back to pending
            $settlement = $dispute->booking->vendorSettlement;
            if ($settlement && $settlement->status === SettlementStatus::OnHold) {
                $settlement->update(['status' => SettlementStatus::Pending]);
            }

            $dispute->booking->update(['status' => BookingStatus::Completed]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_rejected_dispute',
                'auditable_type' => Dispute::class,
                'auditable_id' => $dispute->id,
                'new_values' => [
                    'status' => 'rejected',
                    'notes' => $request->input('rejection_notes'),
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.disputes.show', $dispute->id)
            ->with('info', 'Klaim sengketa ditolak. Dana penahanan payout dilepaskan kembali.');
    }

    /**
     * Escalate ordinary dispute to Superadmin (Policy 11.1).
     */
    public function escalate(Request $request, Dispute $dispute): RedirectResponse
    {
        $this->authorizeAdminRegionalAccess($dispute);

        $request->validate([
            'escalation_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::transaction(function () use ($dispute, $request) {
            $dispute->update([
                'is_escalated' => true,
                'status' => DisputeStatus::Escalated,
                'escalated_at' => now(),
                'resolution_notes' => 'Dieskalasikan ke Superadmin: '.$request->input('escalation_reason'),
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_escalated_dispute',
                'auditable_type' => Dispute::class,
                'auditable_id' => $dispute->id,
                'new_values' => [
                    'reason' => $request->input('escalation_reason'),
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.disputes.show', $dispute->id)
            ->with('warning', 'Kasus sengketa berhasil dieskalasikan ke Superadmin untuk investigasi lebih lanjut.');
    }

    /**
     * Superadmin arbitration and optional vendor suspension (Policy 11.2 & 7.3).
     */
    public function arbitrate(Request $request, Dispute $dispute): RedirectResponse
    {
        if (! auth()->user()->isSuperadmin()) {
            abort(403, 'Akses Ditolak: Otoritas arbitrase dan pembekuan akun mitra eksklusif untuk Superadmin.');
        }

        $request->validate([
            'arbitration_decision' => ['required', 'in:resolved,rejected'],
            'resolution_notes' => ['required', 'string', 'min:5', 'max:1000'],
            'compensation_amount' => ['nullable', 'numeric', 'min:0'],
            'vendor_penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'suspend_vendor' => ['nullable', 'boolean'],
            'suspension_reason' => ['nullable', 'required_if:suspend_vendor,1', 'string', 'min:5', 'max:500'],
        ]);

        $decision = $request->input('arbitration_decision');
        $comp = (float) $request->input('compensation_amount', 0);
        $penalty = (float) $request->input('vendor_penalty_amount', 0);
        $shouldSuspend = $request->boolean('suspend_vendor');
        $suspensionReason = $request->input('suspension_reason');

        DB::transaction(function () use ($dispute, $decision, $comp, $penalty, $shouldSuspend, $suspensionReason, $request) {
            $status = $decision === 'resolved' ? DisputeStatus::Resolved : DisputeStatus::Rejected;

            $dispute->update([
                'status' => $status,
                'resolution_notes' => '[Putusan Superadmin] '.$request->input('resolution_notes'),
                'compensation_amount' => $comp,
                'vendor_penalty_amount' => $penalty,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            // Release or adjust settlement
            $settlement = $dispute->booking->vendorSettlement;
            if ($settlement && $settlement->status === SettlementStatus::OnHold) {
                if ($status === DisputeStatus::Resolved) {
                    $newPayout = max(0, $settlement->amount - $penalty);
                    $settlement->update(['amount' => $newPayout, 'status' => SettlementStatus::Pending]);
                } else {
                    $settlement->update(['status' => SettlementStatus::Pending]);
                }
            }

            $dispute->booking->update(['status' => BookingStatus::Completed]);

            // Optional vendor account suspension (Policy 7.3)
            if ($shouldSuspend) {
                $dispute->vendorProfile->update([
                    'is_suspended' => true,
                    'suspended_at' => now(),
                    'suspension_reason' => $suspensionReason,
                    'suspended_by' => auth()->id(),
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'superadmin_arbitrated_dispute',
                'auditable_type' => Dispute::class,
                'auditable_id' => $dispute->id,
                'new_values' => [
                    'decision' => $status->value,
                    'compensation' => $comp,
                    'penalty' => $penalty,
                    'vendor_suspended' => $shouldSuspend,
                    'suspension_reason' => $suspensionReason,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        $msg = 'Arbitrase Superadmin berhasil diputuskan!';
        if ($shouldSuspend) {
            $msg .= ' Akun mitra terkait telah dibekukan (suspended) dari penerimaan booking baru.';
        }

        return redirect()->route('admin.disputes.show', $dispute->id)->with('success', $msg);
    }

    /**
     * Enforce strict regional boundary authorization (Policy 2.1, 13.2).
     */
    protected function authorizeAdminRegionalAccess(Dispute $dispute): void
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return;
        }

        $region = $user->assignedRegion;
        $bookingRegionId = $dispute->booking->listing?->operational_region_id;
        $matchesCity = ! empty($region?->cities) && in_array($dispute->booking->listing?->city, $region->cities, true);

        if (! $region || ($bookingRegionId !== $region->id && ! $matchesCity)) {
            abort(403, 'Akses Ditolak: Anda hanya memiliki kewenangan sengketa pada wilayah kerja operasional Anda.');
        }
    }
}
