<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case Pending = 'pending';
    case Settled = 'settled';
    case Failed = 'failed';

    /**
     * Get human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pencairan Mitra',
            self::Settled => 'Dana Disalurkan ke Mitra',
            self::Failed => 'Pencairan Gagal',
        };
    }
}
