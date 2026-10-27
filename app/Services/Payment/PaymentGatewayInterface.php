<?php

namespace App\Services\Payment;

use App\Models\Booking;
use App\Models\PaymentAttempt;

interface PaymentGatewayInterface
{
    /**
     * Create closeout checkout / invoice for customer retrieval payment.
     */
    public function createCloseoutInvoice(Booking $booking): PaymentAttempt;

    /**
     * Verify incoming webhook authenticity using callback token or signature.
     */
    public function verifyWebhook(array $headers, array $payload): bool;

    /**
     * Process a paid webhook notification idempotently.
     */
    public function processPaidWebhook(array $payload): ?Booking;
}
