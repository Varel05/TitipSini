<?php

namespace App\Enums;

enum VendorVerificationStatus: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    /**
     * Get human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Belum Verifikasi',
            self::Pending => 'Menunggu Verifikasi',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Badge CSS classes for Tailwind.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Unverified => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Pending => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Verified => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Rejected => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    /**
     * Determine if vendor is eligible to receive bookings.
     */
    public function canReceiveBookings(): bool
    {
        return $this === self::Verified;
    }
}
