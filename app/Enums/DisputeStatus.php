<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case Open = 'open';
    case Escalated = 'escalated';
    case Resolved = 'resolved';
    case Rejected = 'rejected';

    /**
     * Human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Kasus Terbuka (Investigasi Admin)',
            self::Escalated => 'Dieskalasikan ke Superadmin',
            self::Resolved => 'Selesai & Kesepakatan Tercapai',
            self::Rejected => 'Ditolak (Klaim Tidak Valid)',
        };
    }

    /**
     * Badge CSS classes for Tailwind.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Open => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Escalated => 'bg-purple-50 text-purple-700 border-purple-200',
            self::Resolved => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Rejected => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }
}
