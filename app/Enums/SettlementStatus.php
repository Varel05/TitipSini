<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case Pending = 'pending';
    case OnHold = 'on_hold';
    case Settled = 'settled';
    case Failed = 'failed';

    /**
     * Get human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pencairan Mitra',
            self::OnHold => 'Ditahan Karena Sengketa (Escrow Hold)',
            self::Settled => 'Dana Disalurkan ke Mitra',
            self::Failed => 'Pencairan Gagal',
        };
    }

    /**
     * Badge CSS classes for Tailwind.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-700 border-amber-200',
            self::OnHold => 'bg-rose-50 text-rose-700 border-rose-200',
            self::Settled => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Failed => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }
}
