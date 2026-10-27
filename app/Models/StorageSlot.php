<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'listing_id',
    'slot_number',
    'size',
    'max_weight_kg',
    'is_active',
    'is_occupied',
    'notes',
])]
class StorageSlot extends Model
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
            'max_weight_kg' => 'decimal:2',
            'is_active' => 'boolean',
            'is_occupied' => 'boolean',
        ];
    }

    /**
     * Get the listing that owns this slot.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Determine if this slot is available for storage.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && ! $this->is_occupied;
    }
}
