<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Created = 'created';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case RetrievalRequested = 'retrieval_requested';
    case ReturnedByVendor = 'returned_by_vendor';
    case Completed = 'completed';
    case Disputed = 'disputed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /**
     * Get human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Created => 'Menunggu Konfirmasi',
            self::Confirmed => 'Siap Dititipkan',
            self::CheckedIn => 'Tersimpan & Tersegel',
            self::RetrievalRequested => 'Pengambilan Diajukan',
            self::ReturnedByVendor => 'Barang Dikembalikan (48 Jam)',
            self::Completed => 'Selesai & Diterima',
            self::Disputed => 'Dalam Sengketa',
            self::Cancelled => 'Dibatalkan',
            self::Expired => 'Kadaluarsa',
        };
    }

    /**
     * Dot indicator color for badges.
     */
    public function dotColorClass(): string
    {
        return match ($this) {
            self::Created => 'bg-amber-500',
            self::Confirmed => 'bg-blue-500',
            self::CheckedIn => 'bg-indigo-500',
            self::RetrievalRequested => 'bg-purple-500',
            self::ReturnedByVendor => 'bg-sky-500',
            self::Completed => 'bg-emerald-500',
            self::Disputed => 'bg-orange-500',
            self::Cancelled => 'bg-rose-500',
            self::Expired => 'bg-slate-400',
        };
    }

    /**
     * Badge CSS classes for Tailwind.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Created => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Confirmed => 'bg-blue-50 text-blue-700 border-blue-200',
            self::CheckedIn => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::RetrievalRequested => 'bg-purple-50 text-purple-700 border-purple-200',
            self::ReturnedByVendor => 'bg-sky-50 text-sky-700 border-sky-200',
            self::Completed => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Disputed => 'bg-orange-50 text-orange-700 border-orange-200',
            self::Cancelled => 'bg-rose-50 text-rose-700 border-rose-200',
            self::Expired => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    /**
     * Determine if customer can cancel this booking.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Created, self::Confirmed], true);
    }

    /**
     * Determine if payment is required at this stage.
     */
    public function requiresPayment(): bool
    {
        return $this === self::RetrievalRequested;
    }
}
