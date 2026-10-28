<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\User;
use App\Services\BookingService;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected PaymentGatewayInterface $paymentGateway,
    ) {}

    /**
     * Show booking form for a listing.
     */
    public function create(Listing $listing): View
    {
        abort_unless($listing->canAcceptBooking(), 404, 'Tempat penitipan ini sedang tidak menerima pesanan.');

        $listing->load(['vendorProfile', 'category', 'facilities']);

        return view('pages.booking.create', [
            'listing' => $listing,
        ]);
    }

    /**
     * Store new booking (Zero upfront payment).
     */
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:25'],
            'customer_email' => ['required', 'email'],
            'duration_type' => ['required', 'in:day,hour'],
            'duration_units' => ['required', 'integer', 'min:1', 'max:60'],
            'total_items' => ['required', 'integer', 'min:1', 'max:10'],
            'check_in_plan_at' => ['required', 'date', 'after_or_equal:today'],
            'item_name' => ['required', 'string', 'max:150'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Resolve or create customer account
        $customer = auth()->user() ?? User::firstOrCreate(
            ['email' => $validated['customer_email']],
            [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
                'password' => bcrypt(Str::random(16)),
            ]
        );

        if (! auth()->check()) {
            auth()->login($customer);
        }

        $booking = $this->bookingService->createBooking($customer, $listing, [
            'duration_type' => $validated['duration_type'],
            'duration_units' => $validated['duration_units'],
            'total_items' => $validated['total_items'],
            'check_in_plan_at' => $validated['check_in_plan_at'],
            'item_name' => $validated['item_name'],
            'customer_notes' => $validated['customer_notes'] ?? null,
        ]);

        return redirect()->route('booking.show', $booking->booking_code)
            ->with('success', 'Reservasi berhasil dibuat! Tidak ada uang muka. Tunjukkan tiket ini saat tiba di lokasi mitra.');
    }

    /**
     * Show booking details, QR code, and current status.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['customer', 'listing.category', 'vendorProfile', 'items', 'paymentAttempts', 'vendorSettlement']);

        return view('pages.booking.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Customer requests retrieval & generates payment session via Xendit.
     */
    public function requestRetrieval(Booking $booking): RedirectResponse
    {
        $this->bookingService->requestRetrieval($booking);

        // Generate Xendit invoice for retrieval closeout
        try {
            $payment = $this->paymentGateway->createCloseoutInvoice($booking);
        } catch (\DomainException $e) {
            return redirect()->route('booking.show', $booking->booking_code)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('booking.show', $booking->booking_code)
            ->with('info', 'Permintaan pengambilan berhasil dikirim. Silakan selesaikan pembayaran tagihan penitipan Anda.');
    }

    /**
     * Initiate payment redirect to Xendit checkout.
     */
    public function pay(Booking $booking): RedirectResponse
    {
        try {
            $payment = $this->paymentGateway->createCloseoutInvoice($booking);
        } catch (\DomainException $e) {
            return redirect()->route('booking.show', $booking->booking_code)
                ->with('error', $e->getMessage());
        }

        if (! empty($payment->checkout_url)) {
            return redirect()->away($payment->checkout_url);
        }

        return redirect()->route('booking.show', $booking->booking_code)
            ->with('error', 'Gagal memuat sesi pembayaran Xendit. Silakan coba kembali.');
    }

    /**
     * Display sandbox payment simulation page for demonstration and testing.
     */
    public function checkoutSimulation(Request $request, Booking $booking): View
    {
        $booking->load(['customer', 'listing', 'vendorProfile', 'paymentAttempts']);
        $payment = $booking->paymentAttempts()->latest()->first();

        return view('pages.booking.checkout-simulation', [
            'booking' => $booking,
            'payment' => $payment,
            'ref' => $request->query('ref', $payment?->payment_reference ?? 'REF-SIMULATION'),
        ]);
    }

    /**
     * Simulate payment success callback (for local test & demo sandbox).
     */
    public function simulatePaymentSuccess(Booking $booking): RedirectResponse
    {
        $payment = $booking->paymentAttempts()->latest()->first();

        if (! $payment) {
            $payment = $this->paymentGateway->createCloseoutInvoice($booking);
        }

        // Trigger processing via payment gateway to ensure split & settlement executes
        $this->paymentGateway->processPaidWebhook([
            'status' => 'PAID',
            'external_id' => $payment->payment_reference,
            'amount' => $payment->amount,
            'payment_method' => 'QRIS_SANDBOX',
        ]);

        return redirect()->route('booking.show', $booking->booking_code)
            ->with('success', 'Pembayaran Rp '.number_format($payment->amount, 0, ',', '.').' berhasil diverifikasi via Xendit! Split komisi 20% & payout vendor berhasil dicatat.');
    }

    /**
     * Simulate physical check-in (drop & seal) for demo/vendor testing.
     */
    public function simulateCheckIn(Booking $booking): RedirectResponse
    {
        $sealCode = 'SEAL-'.strtoupper(Str::random(8));
        $this->bookingService->checkIn($booking, $sealCode);

        return redirect()->route('booking.show', $booking->booking_code)
            ->with('success', "Barang berhasil di-check-in dengan kode segel resmi: {$sealCode}.");
    }

    /**
     * Cancel booking by customer before check-in.
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $reason = $request->input('reason', 'Dibatalkan oleh pelanggan.');
        $this->bookingService->cancelBooking($booking, $reason);

        return redirect()->route('booking.show', $booking->booking_code)
            ->with('info', 'Pesanan penitipan telah dibatalkan.');
    }

    /**
     * Customer confirms receipt of returned goods (Policy 8.2).
     */
    public function confirmReturn(Booking $booking): RedirectResponse
    {
        $this->bookingService->confirmReturn($booking);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'customer_confirmed_return_completed',
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'new_values' => ['status' => 'completed'],
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('booking.show', $booking->booking_code)
            ->with('success', 'Terima kasih telah mengonfirmasi penerimaan barang Anda! Layanan penitipan telah selesai.');
    }

    /**
     * List user bookings.
     */
    public function index(): View
    {
        $user = auth()->user();

        $bookings = Booking::query()
            ->where('customer_id', $user->id)
            ->with(['listing', 'vendorProfile'])
            ->latest()
            ->paginate(10);

        return view('pages.booking.index', [
            'bookings' => $bookings,
        ]);
    }
}
