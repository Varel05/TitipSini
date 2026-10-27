<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';

    /**
     * Get human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Lunas Terverifikasi',
            self::Failed => 'Gagal',
            self::Expired => 'Kadaluarsa',
            self::Refunded => 'Dikembalikan (Refund)',
        };
    }

    /**
     * Determine if payment is successfully completed.
     */
    public function isPaid(): bool
    {
        return $this === self::Paid;
    }
}
