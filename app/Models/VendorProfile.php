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
    'revision_notes',
    'id_card_path',
    'business_document_path',
    'skck_path',
    'skck_issued_at',
    'skck_expires_at',
    'skck_grace_period_ends_at',
    'skck_verified_at',
    'skck_status',
    'skck_pruned_at',
    'privacy_anonymized_at',
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
            'skck_issued_at' => 'date',
            'skck_expires_at' => 'date',
            'skck_grace_period_ends_at' => 'date',
            'skck_verified_at' => 'datetime',
            'skck_pruned_at' => 'datetime',
            'privacy_anonymized_at' => 'datetime',
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
     * Get the SKCK access requests submitted by admins for this vendor.
     */
    public function skckAccessRequests(): HasMany
    {
        return $this->hasMany(SkckAccessRequest::class);
    }

    /**
     * Determine if the vendor is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === VendorVerificationStatus::Verified;
    }

    /**
     * Determine if the vendor is pending revision from admin feedback.
     */
    public function needsRevision(): bool
    {
        return $this->verification_status === VendorVerificationStatus::NeedsRevision;
    }

    /**
     * Determine if the SKCK certificate has passed its formal expiration date.
     */
    public function isSkckExpired(): bool
    {
        return $this->skck_expires_at !== null && $this->skck_expires_at->isPast();
    }

    /**
     * Determine if the vendor is currently within the 30-day grace period post-expiration.
     * Policy 6.2: Vendor still allowed to accept bookings during 30-day grace period.
     */
    public function isInSkckGracePeriod(): bool
    {
        if (! $this->isSkckExpired()) {
            return false;
        }

        $graceEnd = $this->skck_grace_period_ends_at
            ?? ($this->skck_expires_at ? $this->skck_expires_at->copy()->addDays(30) : null);

        return $graceEnd !== null && ! $graceEnd->isPast();
    }

    /**
     * Determine if the 30-day SKCK grace period has completely lapsed.
     * Policy 6.2: Vendor is barred from accepting new bookings once grace period ends.
     */
    public function isSkckBlocked(): bool
    {
        if (! $this->isSkckExpired()) {
            return false;
        }

        $graceEnd = $this->skck_grace_period_ends_at
            ?? ($this->skck_expires_at ? $this->skck_expires_at->copy()->addDays(30) : null);

        return $graceEnd === null || $graceEnd->isPast();
    }

    /**
     * Determine if the vendor is eligible to accept bookings.
     */
    public function canAcceptBookings(): bool
    {
        return $this->isVerified() && $this->is_open && ! $this->isSkckBlocked();
    }

    /**
     * Check if a specific admin has active authorized access to view the raw SKCK document.
     */
    public function hasActiveSkckAccessFor(User $user): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return $this->skckAccessRequests()
            ->where('admin_id', $user->id)
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Mask NIK / sensitive identity numbers (e.g. 317101******0001).
     */
    public static function maskIdentity(?string $raw): string
    {
        if (! $raw || strlen($raw) < 8) {
            return '****************';
        }

        $start = substr($raw, 0, 4);
        $end = substr($raw, -4);
        $maskedLength = max(4, strlen($raw) - 8);

        return $start.str_repeat('*', $maskedLength).$end;
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
