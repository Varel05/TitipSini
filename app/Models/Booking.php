<?php

namespace App\Models;

use App\Enums\BookingStatus;
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
])]
class Booking extends Model
{
    use HasFactory;

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
