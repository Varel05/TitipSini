<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Vendor = 'vendor';
    case Admin = 'admin';
    case Superadmin = 'superadmin';

    /**
     * Get human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Pelanggan',
            self::Vendor => 'Mitra Vendor',
            self::Admin => 'Administrator',
            self::Superadmin => 'Super Administrator',
        };
    }

    /**
     * Determine if role is administrative.
     */
    public function isAdministrative(): bool
    {
        return in_array($this, [self::Admin, self::Superadmin], true);
    }
}
