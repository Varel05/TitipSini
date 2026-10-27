<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'icon'])]
class Facility extends Model
{
    use HasFactory;

    /**
     * Get listings that provide this facility.
     */
    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'facility_listing');
    }
}
