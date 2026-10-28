<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\DisputeCategory;
use App\Enums\DisputeStatus;
use App\Enums\SettlementStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Dispute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerDisputeController extends Controller
{
    /**
     * Show form to file a dispute/complaint for a booking.
     */
    public function create(Booking $booking): View
    {
        $this->authorizeCustomer($booking);

        return view('pages.booking.dispute-create', [
            'booking' => $booking,
            'categories' => DisputeCategory::cases(),
        ]);
    }

    /**
     * Submit and record a new dispute (Policy 11.1).
     */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeCustomer($booking);

        $request->validate([
            'category' => ['required', 'string'],
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $category = DisputeCategory::from($request->input('category'));
        $isEscalated = $category->requiresSuperadminEscalation();

        DB::transaction(function () use ($booking, $category, $isEscalated, $request) {
            $dispute = Dispute::create([
                'booking_id' => $booking->id,
                'customer_id' => auth()->id() ?? $booking->customer_id,
                'vendor_profile_id' => $booking->vendor_profile_id,
                'category' => $category,
                'status' => $isEscalated ? DisputeStatus::Escalated : DisputeStatus::Open,
                'is_escalated' => $isEscalated,
                'escalated_at' => $isEscalated ? now() : null,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
            ]);

            // Update booking status to Disputed
            $booking->update(['status' => BookingStatus::Disputed]);

            // Hold vendor payout (Escrow Hold) per Policy 11.1 Poin 5
            if ($booking->vendorSettlement && $booking->vendorSettlement->status === SettlementStatus::Pending) {
                $booking->vendorSettlement->update(['status' => SettlementStatus::OnHold]);
            }

            AuditLog::create([
                'user_id' => auth()->id() ?? $booking->customer_id,
                'action' => 'customer_filed_dispute',
                'auditable_type' => Dispute::class,
                'auditable_id' => $dispute->id,
                'new_values' => [
                    'booking_code' => $booking->booking_code,
                    'category' => $category->value,
                    'is_escalated' => $isEscalated,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        $msg = $isEscalated
            ? 'Laporan kendala kritis telah berhasil dicatat dan langsung dieskalasikan ke Superadmin untuk investigasi mendalam.'
            : 'Laporan kendala Anda telah diterima. Admin Wilayah akan segera meninjau dan menengahi kasus ini.';

        return redirect()->route('booking.show', $booking->booking_code)->with('warning', $msg);
    }

    /**
     * Ensure the user is authorized to file a dispute on this booking.
     */
    protected function authorizeCustomer(Booking $booking): void
    {
        $user = auth()->user();
        if ($user && $user->id !== $booking->customer_id && ! $user->isAdmin()) {
            abort(403, 'Akses Ditolak: Anda bukan pemilik reservasi ini.');
        }
    }
}
