<?php

namespace App\Services\Payment;

use App\Enums\SettlementStatus;
use App\Models\AuditLog;
use App\Models\VendorSettlement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VendorPayoutService
{
    /**
     * Trigger disbursement payout for vendor settlement share (80% net earnings).
     */
    public function processPayout(VendorSettlement $settlement): VendorSettlement
    {
        if ($settlement->status === SettlementStatus::Settled) {
            return $settlement;
        }

        $vendorProfile = $settlement->vendorProfile;
        $amount = (float) $settlement->amount;
        $payoutRef = 'XEN-DISB-'.strtoupper(uniqid()).'-'.$settlement->id;

        $secretKey = config('services.xendit.secret_key');
        $isProduction = config('services.xendit.is_production', false);

        // If credentials are real and production is active, call Xendit Disbursement API
        if ($isProduction && $secretKey && ! str_starts_with($secretKey, 'xnd_development_dummy')) {
            try {
                $response = Http::withBasicAuth($secretKey, '')
                    ->post('https://api.xendit.co/disbursements', [
                        'external_id' => $payoutRef,
                        'amount' => (int) round($amount),
                        'bank_code' => $vendorProfile->bank_name ?? 'BCA',
                        'account_holder_name' => $vendorProfile->bank_account_holder ?? $vendorProfile->store_name,
                        'account_number' => $vendorProfile->bank_account_number ?? '1234567890',
                        'description' => "TitipSini Vendor Payout #{$settlement->booking_id}",
                    ]);

                if ($response->successful()) {
                    $resData = $response->json();
                    $settlement->update([
                        'status' => SettlementStatus::Settled,
                        'payout_reference' => $resData['id'] ?? $payoutRef,
                        'provider_response' => $resData,
                        'settled_at' => now(),
                    ]);
                } else {
                    Log::warning('Xendit disbursement non-success response', ['body' => $response->body()]);
                    $settlement->update([
                        'payout_reference' => $payoutRef,
                        'provider_response' => $response->json(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Xendit disbursement exception: '.$e->getMessage());
            }
        } else {
            // Local sandbox / test environment automatic settlement simulation
            $settlement->update([
                'status' => SettlementStatus::Settled,
                'payout_reference' => $payoutRef,
                'provider_response' => [
                    'mode' => 'sandbox_simulation',
                    'disbursement_id' => $payoutRef,
                    'amount' => $amount,
                    'disbursed_at' => now()->toIso8601String(),
                ],
                'settled_at' => now(),
            ]);
        }

        AuditLog::create([
            'user_id' => $vendorProfile->user_id ?? null,
            'action' => 'vendor_payout_disbursed',
            'auditable_type' => VendorSettlement::class,
            'auditable_id' => $settlement->id,
            'new_values' => [
                'amount' => $amount,
                'payout_reference' => $settlement->payout_reference,
                'status' => $settlement->status->value,
            ],
            'ip_address' => '127.0.0.1',
        ]);

        return $settlement->fresh();
    }
}
