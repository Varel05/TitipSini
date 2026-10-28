<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'cycle_number',
    'payment_reference',
    'channel',
    'amount',
    'status',
    'checkout_url',
    'qr_string',
    'provider_response',
    'expires_at',
    'paid_at',
])]
class PaymentAttempt extends Model
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
            'cycle_number' => 'integer',
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'provider_response' => 'array',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the booking associated with the payment attempt.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Determine if this attempt is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    /**
     * Determine if this attempt has expired.
     */
    public function isExpired(): bool
    {
        if ($this->status === PaymentStatus::Expired) {
            return true;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
