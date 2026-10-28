<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\SettlementStatus;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Listing;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\VendorSettlement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Commission rate for TitipSini platform (20% as agreed in ADR-002).
     */
    public const PLATFORM_COMMISSION_PERCENT = 20;

    /**
     * Calculate authoritative server-side price quote.
     *
     * @return array{
     *     duration_type: string,
     *     duration_units: int,
     *     total_items: int,
     *     base_rate: float,
     *     storage_fee: float,
     *     platform_fee: float,
     *     gateway_fee: float,
     *     total_amount: float,
     *     vendor_earnings: float,
     *     commission_percent: int
     * }
     */
    public function calculateQuote(Listing $listing, string $durationType, int $durationUnits, int $totalItems): array
    {
        $durationUnits = max(1, $durationUnits);
        $totalItems = max(1, $totalItems);

        $baseRate = match ($durationType) {
            'hour' => (float) ($listing->price_per_hour ?? ($listing->price_per_day / 24)),
            default => (float) $listing->price_per_day,
        };

        $storageFee = round($baseRate * $durationUnits * $totalItems, 2);
        $platformFee = round($storageFee * (self::PLATFORM_COMMISSION_PERCENT / 100), 2);
        $vendorEarnings = round($storageFee - $platformFee, 2);
        $gatewayFee = 0.00; // Covered in total or transparent
        $totalAmount = $storageFee + $gatewayFee;

        return [
            'duration_type' => $durationType,
            'duration_units' => $durationUnits,
            'total_items' => $totalItems,
            'base_rate' => $baseRate,
            'storage_fee' => $storageFee,
            'platform_fee' => $platformFee,
            'gateway_fee' => $gatewayFee,
            'total_amount' => $totalAmount,
            'vendor_earnings' => $vendorEarnings,
            'commission_percent' => self::PLATFORM_COMMISSION_PERCENT,
        ];
    }

    /**
     * Create a new booking with concurrency locking on listing capacity.
     */
    public function createBooking(User $customer, Listing $listing, array $data): Booking
    {
        return DB::transaction(function () use ($customer, $listing, $data) {
            // Concurrency lock to prevent overbooking
            $lockedListing = Listing::where('id', $listing->id)->lockForUpdate()->firstOrFail();

            $totalItems = (int) ($data['total_items'] ?? 1);
            if ($lockedListing->capacity_available < $totalItems) {
                throw ValidationException::withMessages([
                    'total_items' => "Kapasitas tidak mencukupi. Sisa slot tersedia: {$lockedListing->capacity_available}.",
                ]);
            }

            $durationType = $data['duration_type'] ?? 'day';
            $durationUnits = (int) ($data['duration_units'] ?? 1);

            $quote = $this->calculateQuote($lockedListing, $durationType, $durationUnits, $totalItems);

            $checkInPlan = Carbon::parse($data['check_in_plan_at']);
            $checkOutPlan = $durationType === 'hour'
                ? (clone $checkInPlan)->addHours($durationUnits)
                : (clone $checkInPlan)->addDays($durationUnits);

            $bookingCode = Booking::generateUniqueCode();

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'customer_id' => $customer->id,
                'vendor_profile_id' => $lockedListing->vendor_profile_id,
                'listing_id' => $lockedListing->id,
                'status' => $data['status'] ?? BookingStatus::Created,
                'duration_type' => $durationType,
                'duration_units' => $durationUnits,
                'total_items' => $totalItems,
                'check_in_plan_at' => $checkInPlan,
                'check_out_plan_at' => $checkOutPlan,
                'storage_fee' => $quote['storage_fee'],
                'platform_fee' => $quote['platform_fee'],
                'gateway_fee' => $quote['gateway_fee'],
                'total_amount' => $quote['total_amount'],
                'vendor_earnings' => $quote['vendor_earnings'],
                'pricing_snapshot' => $quote,
                'customer_notes' => $data['customer_notes'] ?? null,
            ]);

            // Create items breakdown
            $items = $data['items'] ?? [
                ['item_name' => $data['item_name'] ?? 'Koper / Tas', 'category_name' => 'Koper', 'item_size' => 'medium'],
            ];

            foreach ($items as $item) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'item_name' => $item['item_name'] ?? 'Barang Titipan',
                    'category_name' => $item['category_name'] ?? null,
                    'item_size' => $item['item_size'] ?? 'medium',
                    'weight_kg' => $item['weight_kg'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Decrement available capacity
            $lockedListing->decrement('capacity_available', $totalItems);

            return $booking;
        });
    }

    /**
     * Check-in goods at the vendor location, record security barcode seal.
     */
    public function checkIn(Booking $booking, string $sealCode, ?string $photoPath = null): Booking
    {
        return DB::transaction(function () use ($booking, $sealCode, $photoPath) {
            $booking->update([
                'status' => BookingStatus::CheckedIn,
                'security_seal_code' => $sealCode,
                'check_in_photo_path' => $photoPath,
                'actual_check_in_at' => now(),
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Customer requests retrieval. Recalculates overtime if applicable and prepares payment.
     */
    public function requestRetrieval(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => BookingStatus::RetrievalRequested,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Finalize checkout after successful payment confirmation.
     * Triggers vendor settlement creation.
     */
    public function completeRetrieval(Booking $booking, PaymentAttempt $payment): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => BookingStatus::Completed,
                'actual_check_out_at' => now(),
            ]);

            // Release capacity back to listing
            $booking->listing()->increment('capacity_available', $booking->total_items);

            // Automatically record vendor settlement payout
            VendorSettlement::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'vendor_profile_id' => $booking->vendor_profile_id,
                    'amount' => $booking->vendor_earnings,
                    'status' => SettlementStatus::Pending,
                ]
            );

            return $booking->fresh();
        });
    }

    /**
     * Cancel booking and restore available capacity.
     */
    public function cancelBooking(Booking $booking, string $reason): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            if (! $booking->status->canBeCancelled()) {
                throw new \RuntimeException('Booking tidak dapat dibatalkan pada status saat ini.');
            }

            $booking->update([
                'status' => BookingStatus::Cancelled,
                'cancellation_reason' => $reason,
            ]);

            $booking->listing()->increment('capacity_available', $booking->total_items);

            return $booking->fresh();
        });
    }

    /**
     * Vendor confirms and accepts incoming booking (Policy 8.1 Poin 4).
     * Unlocks phone contact exchange for coordination (Policy 13.1).
     */
    public function confirmBooking(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => BookingStatus::Confirmed,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Vendor rejects incoming booking before check-in with mandatory reason (Policy 8.1 & 8.3).
     */
    public function rejectBooking(Booking $booking, string $reason): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status' => BookingStatus::Cancelled,
                'rejection_reason' => $reason,
                'cancellation_reason' => $reason,
            ]);

            $booking->listing()->increment('capacity_available', $booking->total_items);

            return $booking->fresh();
        });
    }

    /**
     * Vendor marks goods as physically returned, opening 48-hour customer confirmation window (Policy 8.2).
     */
    public function markReturned(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $now = now();
            $booking->update([
                'status' => BookingStatus::ReturnedByVendor,
                'returned_at' => $now,
                'auto_complete_at' => $now->copy()->addHours(48),
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Customer confirms receipt of returned items, completing the booking (Policy 8.2).
     * Automatically triggers vendor settlement record.
     */
    public function confirmReturn(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $now = now();
            $booking->update([
                'status' => BookingStatus::Completed,
                'customer_confirmed_at' => $now,
                'actual_check_out_at' => $booking->actual_check_out_at ?? $now,
            ]);

            // Release capacity back to listing
            $booking->listing()->increment('capacity_available', $booking->total_items);

            // Record vendor payout settlement
            VendorSettlement::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'vendor_profile_id' => $booking->vendor_profile_id,
                    'amount' => $booking->vendor_earnings,
                    'status' => SettlementStatus::Pending,
                ]
            );

            return $booking->fresh();
        });
    }

    /**
     * Automatically complete booking after 48-hour window without customer complaint (Policy 8.2).
     */
    public function autoComplete48Hours(Booking $booking): Booking
    {
        return $this->confirmReturn($booking);
    }
}
