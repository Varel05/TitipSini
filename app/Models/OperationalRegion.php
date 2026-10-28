<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'code',
    'admin_id',
    'provinces',
    'cities',
    'is_active',
    'is_under_supervision',
    'supervision_notes',
])]
class OperationalRegion extends Model
{
    use HasFactory;

    /**
     * Get attributes cast types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provinces' => 'array',
            'cities' => 'array',
            'is_active' => 'boolean',
            'is_under_supervision' => 'boolean',
        ];
    }

    /**
     * The assigned regional active admin.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Listings located in this operational region.
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * Check if this operational region covers a given city name.
     */
    public function coversCity(?string $cityName): bool
    {
        if (! $cityName) {
            return false;
        }

        $cities = $this->cities ?? [];

        foreach ($cities as $city) {
            if (strcasecmp($city, $cityName) === 0 || str_contains(strtolower($cityName), strtolower($city))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if this region currently has an active assigned admin.
     */
    public function hasActiveAdmin(): bool
    {
        return $this->admin_id !== null && ($this->admin?->is_active ?? false);
    }

    /**
     * Scope for active operational regions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for regions under Superadmin supervision (takeover mode).
     */
    public function scopeUnderSupervision(Builder $query): Builder
    {
        return $query->where('is_under_supervision', true);
    }
}
