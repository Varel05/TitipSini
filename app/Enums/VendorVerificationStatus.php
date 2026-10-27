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
     * Determine if vendor is eligible to receive bookings.
     */
    public function canReceiveBookings(): bool
    {
        return $this === self::Verified;
    }
}
