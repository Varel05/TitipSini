<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'booking_code',
    'customer_id',
    'vendor_profile_id',
    'listing_id',
    'storage_slot_id',
    'status',
    'duration_type',
    'duration_units',
    'total_items',
    'check_in_plan_at',
    'check_out_plan_at',
    'actual_check_in_at',
    'actual_check_out_at',
    'storage_fee',
    'platform_fee',
    'gateway_fee',
    'total_amount',
    'vendor_earnings',
    'pricing_snapshot',
    'security_seal_code',
    'check_in_photo_path',
    'check_out_photo_path',
    'customer_notes',
    'cancellation_reason',
    'rejection_reason',
    'returned_at',
    'auto_complete_at',
    'customer_confirmed_at',
    'current_payment_cycle',
    'payment_cycle_reopened_at',
    'payment_reopen_reason',
    'payment_reopened_by',
])]
class Booking extends Model
{
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'current_payment_cycle' => 1,
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'booking_code';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'pricing_snapshot' => 'array',
            'storage_fee' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'gateway_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'vendor_earnings' => 'decimal:2',
            'check_in_plan_at' => 'datetime',
            'check_out_plan_at' => 'datetime',
            'actual_check_in_at' => 'datetime',
            'actual_check_out_at' => 'datetime',
            'returned_at' => 'datetime',
            'auto_complete_at' => 'datetime',
            'customer_confirmed_at' => 'datetime',
            'current_payment_cycle' => 'integer',
            'payment_cycle_reopened_at' => 'datetime',
        ];
    }

    /**
     * Generate unique booking code.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = 'TS-'.date('Ymd').'-'.strtoupper(Str::random(5));
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Get the customer that made the booking.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the vendor profile for the booking.
     */
    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get the listing associated with the booking.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the storage slot allocated for this booking.
     */
    public function storageSlot(): BelongsTo
    {
        return $this->belongsTo(StorageSlot::class);
    }

    /**
     * Get the physical items recorded for this booking.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    /**
     * Get the payment attempts for this booking.
     */
    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    /**
     * Get the vendor settlement record for this booking.
     */
    public function vendorSettlement(): HasOne
    {
        return $this->hasOne(VendorSettlement::class);
    }

    /**
     * Determine if booking is currently checked in.
     */
    public function isCheckedIn(): bool
    {
        return $this->status === BookingStatus::CheckedIn;
    }

    /**
     * Determine if booking requires customer payment for retrieval.
     */
    public function isPendingPayment(): bool
    {
        return $this->status === BookingStatus::RetrievalRequested;
    }

    /**
     * Determine if booking is finalized and completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === BookingStatus::Completed;
    }

    /**
     * Determine if booking is awaiting customer confirmation after vendor returns goods.
     * Policy 8.2: Customer has 48-hour window to confirm or dispute.
     */
    public function isAwaitingCustomerConfirmation(): bool
    {
        return $this->status === BookingStatus::ReturnedByVendor;
    }

    /**
     * Determine if customer & vendor phone contacts are mutually visible.
     * Policy 13.1: Contacts are visible ONLY after booking is confirmed and while actively in service.
     * Hidden during initial creation (pending confirmation) and once completed/cancelled.
     */
    public function canViewContacts(): bool
    {
        return in_array($this->status, [
            BookingStatus::Confirmed,
            BookingStatus::CheckedIn,
            BookingStatus::RetrievalRequested,
            BookingStatus::ReturnedByVendor,
        ], true);
    }

    /**
     * Get displayable customer phone respecting privacy rules (Policy 13.1).
     */
    public function getCustomerPhoneDisplay(): string
    {
        if (! $this->canViewContacts()) {
            return 'Nomor kontak disembunyikan (tersedia setelah booking dikonfirmasi)';
        }

        return $this->customer?->phone ?? '-';
    }

    /**
     * Get displayable vendor phone respecting privacy rules (Policy 13.1).
     */
    public function getVendorPhoneDisplay(): string
    {
        if (! $this->canViewContacts()) {
            return 'Nomor kontak tersedia setelah pesanan dikonfirmasi mitra';
        }

        return $this->vendorProfile?->phone ?? $this->vendorProfile?->user?->phone ?? '-';
    }

    /**
     * Get the admin user who reopened the payment cycle.
     */
    public function paymentReopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_reopened_by');
    }

    /**
     * Get count of payment attempts in the current payment cycle (Policy 10.2).
     */
    public function getCurrentCycleAttemptsCount(): int
    {
        return $this->paymentAttempts()
            ->where('cycle_number', $this->current_payment_cycle)
            ->count();
    }

    /**
     * Check if 3 payment attempts limit has been exceeded for the current cycle (Policy 10.2).
     * Exceeded when 3 attempts have been made and all of them are failed or expired (none paid/active).
     */
    public function isPaymentAttemptLimitExceeded(): bool
    {
        $hasPaid = $this->paymentAttempts()
            ->where('cycle_number', $this->current_payment_cycle)
            ->where('status', PaymentStatus::Paid)
            ->exists();

        if ($hasPaid) {
            return false;
        }

        $count = $this->getCurrentCycleAttemptsCount();
        if ($count < 3) {
            return false;
        }

        // If 3 attempts exist, check if the 3rd attempt is still actively pending
        $latest = $this->getLatestAttemptForCurrentCycle();
        if ($latest && $latest->status === PaymentStatus::Pending && ! $latest->isExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Get the latest payment attempt in current cycle.
     */
    public function getLatestAttemptForCurrentCycle(): ?PaymentAttempt
    {
        return $this->paymentAttempts()
            ->where('cycle_number', $this->current_payment_cycle)
            ->latest('id')
            ->first();
    }

    /**
     * Determine if admin can reopen the payment cycle (Policy 10.2).
     * Rule: Limit exceeded (3 attempts), booking unpaid, and 30-minute cooldown has elapsed.
     */
    public function canAdminReopenPayment(): bool
    {
        if (! $this->isPaymentAttemptLimitExceeded()) {
            return false;
        }

        if (in_array($this->status, [BookingStatus::Completed, BookingStatus::Cancelled], true)) {
            return false;
        }

        $latest = $this->getLatestAttemptForCurrentCycle();
        if (! $latest) {
            return false;
        }

        // Must wait 30 minutes since last attempt was created/updated
        $referenceTime = $latest->expires_at ?? $latest->updated_at;

        return $referenceTime->copy()->addMinutes(30)->isPast();
    }

    /**
     * Get cooldown seconds remaining before admin can reopen payment (Policy 10.2).
     */
    public function getReopenCooldownSecondsRemaining(): int
    {
        if (! $this->isPaymentAttemptLimitExceeded()) {
            return 0;
        }

        $latest = $this->getLatestAttemptForCurrentCycle();
        if (! $latest) {
            return 0;
        }

        $referenceTime = $latest->expires_at ?? $latest->updated_at;
        $canReopenAt = $referenceTime->copy()->addMinutes(30);

        return max(0, (int) now()->diffInSeconds($canReopenAt, false));
    }

    /**
     * Scope query to only include bookings within the admin's assigned operational region.
     * Superadmin has unrestricted nationwide access.
     */
    public function scopeForAdminRegion(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperadmin()) {
            return $query;
        }

        $region = $user->assignedRegion;
        if (! $region) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('listing', function ($lq) use ($region) {
            $lq->where('operational_region_id', $region->id);
            if (! empty($region->cities)) {
                $lq->orWhereIn('city', $region->cities);
            }
        });
    }
}
