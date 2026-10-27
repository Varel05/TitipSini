<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Superadmin User
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@titipsini.com'],
            [
                'name' => 'Super Administrator TitipSini',
                'password' => Hash::make('password123'),
                'role' => UserRole::Superadmin,
                'phone' => '081234567890',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // 2. Operational Admin User
        $admin = User::firstOrCreate(
            ['email' => 'ops@titipsini.com'],
            [
                'name' => 'Admin Operasional',
                'password' => Hash::make('password123'),
                'role' => UserRole::Admin,
                'phone' => '081234567891',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // 3. Mitra Vendor User & Profile
        $vendorUser = User::firstOrCreate(
            ['email' => 'vendor@titipsini.com'],
            [
                'name' => 'Budi Santoso (Mitra)',
                'password' => Hash::make('password123'),
                'role' => UserRole::Vendor,
                'phone' => '081298765432',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        VendorProfile::firstOrCreate(
            ['user_id' => $vendorUser->id],
            [
                'business_name' => 'Titip Amanah Jaya',
                'business_type' => 'umkm',
                'description' => 'Penyedia penitipan barang & koper terpercaya di pusat kota Jakarta Pusat, dekat stasiun Gambir.',
                'phone' => '081298765432',
                'address' => 'Jl. Kebon Sirih No. 45, Gambir',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'postal_code' => '10110',
                'latitude' => -6.1824,
                'longitude' => 106.8294,
                'verification_status' => VendorVerificationStatus::Verified,
                'verified_at' => now(),
                'verified_by' => $superadmin->id,
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_holder' => 'Budi Santoso',
                'submerchant_id' => 'SUB-VND-001',
                'is_open' => true,
            ]
        );

        // 4. Customer User & Profile
        $customerUser = User::firstOrCreate(
            ['email' => 'customer@titipsini.com'],
            [
                'name' => 'Siti Rahma',
                'password' => Hash::make('password123'),
                'role' => UserRole::Customer,
                'phone' => '085712345678',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        CustomerProfile::firstOrCreate(
            ['user_id' => $customerUser->id],
            [
                'phone' => '085712345678',
                'identity_number' => '3171012345670001',
                'address' => 'Jl. Tebet Barat Dalam No. 12',
                'city' => 'Jakarta Selatan',
                'emergency_contact_name' => 'Ahmad (Keluarga)',
                'emergency_contact_phone' => '085799988877',
            ]
        );
    }
}
