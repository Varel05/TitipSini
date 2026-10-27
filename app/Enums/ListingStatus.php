<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Published = 'published';
    case Archived = 'archived';
    case Rejected = 'rejected';

    /**
     * Get human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Menunggu Moderasi',
            self::Published => 'Dipublikasikan',
            self::Archived => 'Diarsipkan',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Determine if listing is visible to customers.
     */
    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }
}
