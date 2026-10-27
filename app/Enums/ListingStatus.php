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
     * Badge CSS classes for Tailwind.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Submitted => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Published => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Archived => 'bg-slate-100 text-slate-600 border-slate-200',
            self::Rejected => 'bg-rose-50 text-rose-700 border-rose-200',
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
