<?php

namespace App\Models;

use App\Enums\VendorVerificationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'business_name',
    'business_type',
    'description',
    'phone',
    'address',
    'city',
    'province',
    'postal_code',
    'latitude',
    'longitude',
    'verification_status',
    'verified_at',
    'verified_by',
    'rejection_reason',
    'id_card_path',
    'business_document_path',
    'bank_name',
    'bank_account_number',
    'bank_account_holder',
    'submerchant_id',
    'is_open',
])]
class VendorProfile extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verification_status' => VendorVerificationStatus::class,
            'verified_at' => 'datetime',
            'is_open' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Get the user that owns the vendor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the administrator who verified the vendor profile.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the listings owned by this vendor.
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * Determine if the vendor is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === VendorVerificationStatus::Verified;
    }

    /**
     * Determine if the vendor is eligible to accept bookings.
     */
    public function canAcceptBookings(): bool
    {
        return $this->isVerified() && $this->is_open;
    }

    /**
     * Scope query to only include vendors within the admin's assigned operational region.
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

        return $query->where(function ($q) use ($region) {
            if (! empty($region->cities)) {
                $q->whereIn('city', $region->cities);
            }
            $q->orWhereHas('listings', function ($lq) use ($region) {
                $lq->where('operational_region_id', $region->id);
            });
        });
    }

    /**
     * Check if vendor is covered by a specific operational region.
     */
    public function isInRegion(OperationalRegion $region): bool
    {
        if (! empty($region->cities) && in_array($this->city, $region->cities, true)) {
            return true;
        }

        return $this->listings()->where('operational_region_id', $region->id)->exists();
    }
}
