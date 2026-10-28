<?php

namespace App\Enums;

enum DisputeCategory: string
{
    // Sengketa Operasional Biasa (Wewenang Admin Wilayah - Policy 11.1)
    case OperationalDelay = 'operational_delay';
    case DamagedItem = 'damaged_item';
    case LocationIssue = 'location_issue';

    // Kasus Wajib Eskalasi Superadmin (Policy 11.1 Poin 2)
    case LostGoods = 'lost_goods';
    case Fraud = 'fraud';
    case PaymentManipulation = 'payment_manipulation';
    case SafetyThreat = 'safety_threat';
    case CriminalOffense = 'criminal_offense';

    /**
     * Determine if this dispute category requires mandatory escalation to Superadmin (Policy 11.1).
     */
    public function requiresSuperadminEscalation(): bool
    {
        return in_array($this, [
            self::LostGoods,
            self::Fraud,
            self::PaymentManipulation,
            self::SafetyThreat,
            self::CriminalOffense,
        ], true);
    }

    /**
     * Human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::OperationalDelay => 'Keterlambatan Serah Terima Barang',
            self::DamagedItem => 'Kerusakan Fisik Barang / Kemasan',
            self::LocationIssue => 'Kendala Akses Lokasi / Fasilitas',
            self::LostGoods => 'Barang Hilang / Tidak Ditemukan (Wajib Eskalasi)',
            self::Fraud => 'Dugaan Penipuan / Penggelapan (Wajib Eskalasi)',
            self::PaymentManipulation => 'Indikasi Manipulasi Pembayaran (Wajib Eskalasi)',
            self::SafetyThreat => 'Ancaman Keselamatan / Intimidasi (Wajib Eskalasi)',
            self::CriminalOffense => 'Dugaan Tindak Kriminal / Hukum Serius (Wajib Eskalasi)',
        };
    }

    /**
     * Badge CSS classes for Tailwind.
     */
    public function badgeClasses(): string
    {
        if ($this->requiresSuperadminEscalation()) {
            return 'bg-purple-50 text-purple-700 border-purple-200';
        }

        return 'bg-amber-50 text-amber-700 border-amber-200';
    }
}
