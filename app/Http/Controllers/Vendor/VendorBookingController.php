<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\VendorProfile;
use App\Services\BookingService;
use App\Services\Payment\VendorPayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorBookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected VendorPayoutService $vendorPayoutService,
    ) {}

    /**
     * Display list of bookings for this vendor.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();
        abort_unless($vendorProfile, 403, 'Profil Mitra Vendor tidak ditemukan.');

        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $bookings = Booking::where('vendor_profile_id', $vendorProfile->id)
            ->when($statusFilter && $statusFilter !== 'all', function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('booking_code', 'like', "%{$search}%")
                        ->orWhere('security_seal_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->with(['customer', 'listing', 'items', 'paymentAttempts'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('pages.vendor.bookings.index', [
            'vendor' => $vendorProfile,
            'bookings' => $bookings,
            'currentStatus' => $statusFilter ?? 'all',
            'search' => $search,
        ]);
    }

    /**
     * Show booking operational details, physical seal info, and action buttons.
     */
    public function show(Booking $booking): View
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();

        // Ensure authorization: must be the vendor owning this booking, or admin/superadmin
        if ($user->role->value === 'vendor' && $booking->vendor_profile_id !== $vendorProfile?->id) {
            abort(403, 'Akses ditolak. Pesanan ini milik mitra vendor lain.');
        }

        $booking->load(['customer', 'listing', 'items', 'paymentAttempts', 'vendorSettlement']);

        return view('pages.vendor.bookings.show', [
            'booking' => $booking,
            'vendor' => $vendorProfile,
        ]);
    }

    /**
     * Process physical check-in: attach security seal tag and record item condition.
     */
    public function checkIn(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate([
            'security_seal_code' => ['required', 'string', 'max:50'],
            'check_in_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $sealCode = strtoupper(trim($request->input('security_seal_code')));

        // If prefix SEAL- is missing, prepend it
        if (! str_starts_with($sealCode, 'SEAL-')) {
            $sealCode = 'SEAL-'.$sealCode;
        }

        $this->bookingService->checkIn($booking, $sealCode);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'vendor_booking_check_in',
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'new_values' => [
                'security_seal_code' => $sealCode,
                'notes' => $request->input('check_in_notes'),
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('vendor.bookings.show', $booking->booking_code)
            ->with('success', "Barang berhasil di-check-in dengan nomor segel pengaman resmi: {$sealCode}. Simpan barang di loker yang aman.");
    }

    /**
     * Confirm handover and release goods to customer.
     * Enforces strict business rule (ADR-002 section 9.3):
     * Do NOT release physical items unless payment is confirmed PAID!
     */
    public function handover(Request $request, Booking $booking): RedirectResponse
    {
        $latestPayment = $booking->paymentAttempts()->latest()->first();

        // Strict verification: Payment must be Paid
        if (! $latestPayment || $latestPayment->status !== PaymentStatus::Paid) {
            return back()->with('error', 'PERINGATAN: Barang tidak boleh diserahkan! Pembayaran tagihan Xendit belum berstatus Lunas.');
        }

        // Complete the booking lifecycle
        $this->bookingService->completeRetrieval($booking, $latestPayment);

        // Trigger vendor settlement disbursement
        $settlement = $booking->fresh()->vendorSettlement;
        if ($settlement) {
            $this->vendorPayoutService->processPayout($settlement);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'vendor_booking_handover_completed',
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'new_values' => [
                'released_at' => now()->toIso8601String(),
                'verified_payment_ref' => $latestPayment->payment_reference,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('vendor.bookings.show', $booking->booking_code)
            ->with('success', 'Serah terima barang selesai! Status pesanan kini Selesai dan payout Rp '.number_format($booking->vendor_earnings, 0, ',', '.').' diteruskan ke rekening Anda.');
    }
}
