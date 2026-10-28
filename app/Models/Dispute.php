<?php

namespace App\Models;

use App\Enums\DisputeCategory;
use App\Enums\DisputeStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'customer_id',
    'vendor_profile_id',
    'category',
    'status',
    'is_escalated',
    'escalated_at',
    'title',
    'description',
    'evidence_paths',
    'resolution_notes',
    'compensation_amount',
    'vendor_penalty_amount',
    'resolved_by',
    'resolved_at',
])]
class Dispute extends Model
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
            'category' => DisputeCategory::class,
            'status' => DisputeStatus::class,
            'is_escalated' => 'boolean',
            'evidence_paths' => 'array',
            'compensation_amount' => 'decimal:2',
            'vendor_penalty_amount' => 'decimal:2',
            'escalated_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get the booking associated with the dispute.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the customer who filed the dispute.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the vendor profile involved.
     */
    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get the administrator who resolved the dispute.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Determine if this dispute is resolved or closed.
     */
    public function isClosed(): bool
    {
        return in_array($this->status, [DisputeStatus::Resolved, DisputeStatus::Rejected], true);
    }

    /**
     * Scope query to only include disputes within the admin's assigned operational region.
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

        return $query->whereHas('booking.listing', function ($lq) use ($region) {
            $lq->where('operational_region_id', $region->id);
            if (! empty($region->cities)) {
                $lq->orWhereIn('city', $region->cities);
            }
        });
    }
}
