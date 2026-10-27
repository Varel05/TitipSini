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
     * Badge CSS classes for Tailwind.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Paid => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Failed => 'bg-rose-50 text-rose-700 border-rose-200',
            self::Expired => 'bg-slate-100 text-slate-600 border-slate-200',
            self::Refunded => 'bg-indigo-50 text-indigo-700 border-indigo-200',
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
