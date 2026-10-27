<?php

namespace App\Models;

use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'vendor_profile_id',
    'category_id',
    'title',
    'slug',
    'tagline',
    'description',
    'address',
    'city',
    'district',
    'postal_code',
    'latitude',
    'longitude',
    'price_per_hour',
    'price_per_day',
    'price_per_week',
    'price_per_month',
    'capacity_total',
    'capacity_available',
    'status',
    'is_featured',
    'featured_image',
    'rules',
    'operational_hours',
    'published_at',
])]
class Listing extends Model
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
            'status' => ListingStatus::class,
            'price_per_hour' => 'decimal:2',
            'price_per_day' => 'decimal:2',
            'price_per_week' => 'decimal:2',
            'price_per_month' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'capacity_total' => 'integer',
            'capacity_available' => 'integer',
            'is_featured' => 'boolean',
            'operational_hours' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the vendor profile that owns the listing.
     */
    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get the category that the listing belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the facilities associated with the listing.
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'facility_listing');
    }

    /**
     * Get the gallery images for the listing.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    /**
     * Get the physical slots/units for the listing.
     */
    public function slots(): HasMany
    {
        return $this->hasMany(StorageSlot::class);
    }

    /**
     * Alias for slots().
     */
    public function storageSlots(): HasMany
    {
        return $this->slots();
    }

    /**
     * Scope a query to only include published listings.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Published);
    }

    /**
     * Scope a query to only include featured listings.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Determine if listing can accept a new booking.
     */
    public function canAcceptBooking(): bool
    {
        return $this->status === ListingStatus::Published &&
               $this->capacity_available > 0 &&
               ($this->vendorProfile?->canAcceptBookings() ?? false);
    }
}
