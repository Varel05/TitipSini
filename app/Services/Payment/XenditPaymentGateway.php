<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\PaymentAttempt;
use App\Services\BookingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        protected BookingService $bookingService,
        protected VendorPayoutService $vendorPayoutService,
    ) {}

    /**
     * Create closeout checkout / invoice for customer retrieval payment.
     */
    public function createCloseoutInvoice(Booking $booking): PaymentAttempt
    {
        // Check if there is already a pending attempt with a valid checkout url
        $existing = PaymentAttempt::where('booking_id', $booking->id)
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->first();

        if ($existing && ! empty($existing->checkout_url)) {
            return $existing;
        }

        $secretKey = config('services.xendit.secret_key');
        $isProduction = config('services.xendit.is_production', false);
        $externalId = 'TITIPSINI-INV-'.$booking->booking_code.'-'.time();
        $amount = (float) $booking->total_amount;
        $description = "Biaya Penitipan TitipSini #{$booking->booking_code} ({$booking->listing->title})";

        $checkoutUrl = null;
        $providerResponse = [];

        // Real API invocation if configured
        if ($isProduction && $secretKey && ! str_starts_with($secretKey, 'xnd_development_dummy')) {
            try {
                $response = Http::withBasicAuth($secretKey, '')
                    ->post('https://api.xendit.co/v2/invoices', [
                        'external_id' => $externalId,
                        'amount' => (int) round($amount),
                        'description' => $description,
                        'payer_email' => $booking->customer->email ?? 'customer@titipsini.id',
                        'currency' => 'IDR',
                        'customer' => [
                            'given_names' => $booking->customer->name ?? 'Pelanggan TitipSini',
                            'email' => $booking->customer->email ?? 'customer@titipsini.id',
                            'mobile_number' => $booking->customer->phone ?? '08123456789',
                        ],
                        'success_redirect_url' => route('booking.show', $booking->booking_code).'?payment_status=success',
                        'failure_redirect_url' => route('booking.show', $booking->booking_code).'?payment_status=failed',
                    ]);

                if ($response->successful()) {
                    $providerResponse = $response->json();
                    $checkoutUrl = $providerResponse['invoice_url'] ?? null;
                } else {
                    Log::error('Xendit invoice creation error', ['response' => $response->body()]);
                }
            } catch (\Throwable $e) {
                Log::error('Xendit request exception: '.$e->getMessage());
            }
        }

        // Fallback to internal simulation checkout URL for sandbox / local development
        if (empty($checkoutUrl)) {
            $checkoutUrl = route('booking.checkout_simulation', [
                'booking' => $booking->booking_code,
                'ref' => $externalId,
            ]);
            $providerResponse = [
                'mode' => 'sandbox_simulation',
                'external_id' => $externalId,
                'amount' => $amount,
                'status' => 'PENDING',
            ];
        }

        return PaymentAttempt::create([
            'booking_id' => $booking->id,
            'payment_reference' => $externalId,
            'channel' => 'xendit_invoice',
            'amount' => $amount,
            'status' => PaymentStatus::Pending,
            'checkout_url' => $checkoutUrl,
            'provider_response' => $providerResponse,
        ]);
    }

    /**
     * Verify incoming webhook authenticity using callback token or signature.
     */
    public function verifyWebhook(array $headers, array $payload): bool
    {
        $expectedToken = config('services.xendit.webhook_token');
        if (empty($expectedToken)) {
            return true;
        }

        // Check header token variations (X-CALLBACK-TOKEN, x-callback-token)
        $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);
        $token = $normalizedHeaders['x-callback-token'] ?? null;

        if (is_array($token)) {
            $token = $token[0] ?? null;
        }

        return ! empty($token) && hash_equals($expectedToken, (string) $token);
    }

    /**
     * Process a paid webhook notification idempotently.
     */
    public function processPaidWebhook(array $payload): ?Booking
    {
        $status = strtoupper((string) ($payload['status'] ?? ''));
        if (! in_array($status, ['PAID', 'SETTLED'], true)) {
            return null;
        }

        $externalId = $payload['external_id'] ?? null;
        if (! $externalId) {
            return null;
        }

        // Find payment attempt by external_id
        $payment = PaymentAttempt::where('payment_reference', $externalId)->first();

        // If not found directly, attempt to parse booking code from external_id
        if (! $payment && str_starts_with($externalId, 'TITIPSINI-INV-')) {
            $parts = explode('-', $externalId);
            if (isset($parts[2])) {
                $bookingCode = $parts[2];
                $booking = Booking::where('booking_code', $bookingCode)->first();
                if ($booking) {
                    $payment = PaymentAttempt::where('booking_id', $booking->id)->latest()->first();
                }
            }
        }

        if (! $payment) {
            Log::warning('Payment attempt not found for Xendit webhook', ['external_id' => $externalId]);

            return null;
        }

        $booking = $payment->booking;

        // Idempotency: if already paid, avoid duplicate operations
        if ($payment->isPaid()) {
            return $booking;
        }

        // Update payment attempt to Paid
        $payment->update([
            'status' => PaymentStatus::Paid,
            'channel' => $payload['payment_method'] ?? $payload['payment_channel'] ?? 'xendit',
            'provider_response' => $payload,
            'paid_at' => now(),
        ]);

        // Complete retrieval lifecycle and release listing capacity
        $completedBooking = $this->bookingService->completeRetrieval($booking, $payment);

        // Process vendor settlement payout automatically
        $settlement = $completedBooking->vendorSettlement;
        if ($settlement) {
            $this->vendorPayoutService->processPayout($settlement);
        }

        // Record audit trail
        AuditLog::create([
            'user_id' => $booking->customer_id,
            'action' => 'xendit_webhook_payment_paid',
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'new_values' => [
                'external_id' => $externalId,
                'amount' => $payment->amount,
                'channel' => $payment->channel,
            ],
            'ip_address' => '127.0.0.1',
        ]);

        return $completedBooking;
    }
}
