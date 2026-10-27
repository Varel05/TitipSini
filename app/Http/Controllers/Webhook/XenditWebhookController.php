<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    /**
     * Handle incoming webhook notifications from Xendit.
     */
    public function handle(Request $request, PaymentGatewayInterface $paymentGateway): JsonResponse
    {
        Log::info('Xendit webhook received', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        if (! $paymentGateway->verifyWebhook($request->headers->all(), $request->all())) {
            Log::warning('Xendit webhook verification failed: Invalid callback token');

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid callback token',
            ], 403);
        }

        $booking = $paymentGateway->processPaidWebhook($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
            'booking_code' => $booking?->booking_code,
        ]);
    }
}
