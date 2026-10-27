<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'payment_reference',
    'channel',
    'amount',
    'status',
    'checkout_url',
    'qr_string',
    'provider_response',
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
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'provider_response' => 'array',
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
}
