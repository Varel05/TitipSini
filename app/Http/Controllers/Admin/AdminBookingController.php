<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    /**
     * Display list of bookings within the admin's regional boundary (Policy 2.1 & 13.2).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $bookings = Booking::forAdminRegion($user)
            ->with(['customer', 'vendorProfile', 'listing'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('booking_code', 'like', $term)
                        ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', $term))
                        ->orWhereHas('vendorProfile', fn ($vq) => $vq->where('business_name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(15);

        return view('pages.admin.bookings.index', [
            'bookings' => $bookings,
            'user' => $user,
            'assignedRegion' => $user->assignedRegion,
        ]);
    }

    /**
     * Show booking details with payment attempt cycle telemetry (Policy 10.2).
     */
    public function show(Booking $booking): View
    {
        $this->authorizeAdminRegionalAccess($booking);

        $booking->load([
            'customer',
            'vendorProfile.user',
            'listing',
            'storageSlot',
            'paymentAttempts' => fn ($q) => $q->orderBy('cycle_number')->latest(),
            'paymentReopenedBy',
        ]);

        return view('pages.admin.bookings.show', [
            'booking' => $booking,
            'currentCycleAttempts' => $booking->paymentAttempts->where('cycle_number', $booking->current_payment_cycle),
            'canReopen' => $booking->canAdminReopenPayment(),
            'isLimitExceeded' => $booking->isPaymentAttemptLimitExceeded(),
            'cooldownRemainingSeconds' => $booking->getReopenCooldownSecondsRemaining(),
        ]);
    }

    /**
     * Reopen payment cycle after 3 failed attempts & 30-minute cooldown (Policy 10.2).
     * Grants a new cycle with 3 fresh QRIS generation attempts.
     */
    public function reopenPayment(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeAdminRegionalAccess($booking);

        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (! $booking->isPaymentAttemptLimitExceeded()) {
            return back()->with('error', 'Batas 3x percobaan QRIS belum tercapai pada siklus saat ini.');
        }

        if (! $booking->canAdminReopenPayment()) {
            $remaining = (int) ceil($booking->getReopenCooldownSecondsRemaining() / 60);

            return back()->with('error', "Masa tunggu 30 menit belum selesai. Tersisa {$remaining} menit lagi.");
        }

        $reason = $request->input('reason');

        DB::transaction(function () use ($booking, $reason, $request) {
            $booking->increment('current_payment_cycle');
            $booking->update([
                'payment_cycle_reopened_at' => now(),
                'payment_reopen_reason' => $reason,
                'payment_reopened_by' => auth()->id(),
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_reopened_payment_cycle',
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'new_values' => [
                    'new_cycle' => $booking->current_payment_cycle,
                    'reason' => $reason,
                    'reopened_at' => now()->toIso8601String(),
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.bookings.show', $booking->booking_code)
            ->with('success', "Siklus pembayaran ke-{$booking->current_payment_cycle} berhasil dibuka kembali! Kuota 3x percobaan QRIS baru telah diberikan.");
    }

    /**
     * Enforce strict regional boundary authorization (Policy 2.1, 13.2).
     */
    protected function authorizeAdminRegionalAccess(Booking $booking): void
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return;
        }

        $region = $user->assignedRegion;
        $bookingRegionId = $booking->listing?->operational_region_id;
        $matchesCity = ! empty($region?->cities) && in_array($booking->listing?->city, $region->cities, true);

        if (! $region || ($bookingRegionId !== $region->id && ! $matchesCity)) {
            abort(403, 'Akses Ditolak: Anda hanya memiliki kewenangan pada wilayah kerja operasional Anda.');
        }
    }
}
