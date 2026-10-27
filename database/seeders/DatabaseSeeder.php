<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Models\Category;
use App\Models\CustomerProfile;
use App\Models\Facility;
use App\Models\Listing;
use App\Models\StorageSlot;
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

        // 3. Mitra Vendor 1 (Jakarta Pusat)
        $vendorUser1 = User::firstOrCreate(
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

        $vendor1 = VendorProfile::firstOrCreate(
            ['user_id' => $vendorUser1->id],
            [
                'business_name' => 'Titip Amanah Gambir',
                'business_type' => 'umkm',
                'description' => 'Pusat penitipan koper & barang strategis di jantung Jakarta, 2 menit dari pintu keluar Stasiun Gambir.',
                'phone' => '081298765432',
                'address' => 'Jl. Kebon Sirih No. 45, Gambir',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'postal_code' => '10110',
                'latitude' => -6.1768,
                'longitude' => 106.8306,
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

        // 4. Mitra Vendor 2 (Jakarta Selatan - Sudirman)
        $vendorUser2 = User::firstOrCreate(
            ['email' => 'mitra.sudirman@titipsini.com'],
            [
                'name' => 'Hendro Wijaya',
                'password' => Hash::make('password123'),
                'role' => UserRole::Vendor,
                'phone' => '081122334455',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $vendor2 = VendorProfile::firstOrCreate(
            ['user_id' => $vendorUser2->id],
            [
                'business_name' => 'SafeHub Sudirman Central',
                'business_type' => 'pt',
                'description' => 'Fasilitas loker pintar dan safe deposit box profesional dengan standar keamanan perbankan.',
                'phone' => '081122334455',
                'address' => 'Gedung Wisma Sentral Lt. GF, Jl. Jend. Sudirman Kav. 28',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12920',
                'latitude' => -6.2155,
                'longitude' => 106.8214,
                'verification_status' => VendorVerificationStatus::Verified,
                'verified_at' => now(),
                'verified_by' => $superadmin->id,
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '1370009876543',
                'bank_account_holder' => 'PT SafeHub Solusindo',
                'submerchant_id' => 'SUB-VND-002',
                'is_open' => true,
            ]
        );

        // 5. Customer User & Profile
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

        // 6. Categories
        $categoriesData = [
            [
                'name' => 'Koper & Tas Wisata',
                'slug' => 'koper-tas-wisata',
                'description' => 'Penitipan koper kabin, koper bagasi, backpack, dan tas travel dengan proteksi tag segel barcode.',
                'icon' => 'bag-fill',
            ],
            [
                'name' => 'Dokumen & Berkas Penting',
                'slug' => 'dokumen-berkas-penting',
                'description' => 'Penyimpanan map, berkas kantor, sertifikat, dan dokumen kedutaan di safe box khusus tahan air.',
                'icon' => 'file-earmark-lock',
            ],
            [
                'name' => 'Elektronik & Gadget',
                'slug' => 'elektronik-gadget',
                'description' => 'Tempat aman untuk laptop, kamera profesional, tablet, dan perlengkapan audio visual ber-AC.',
                'icon' => 'laptop',
            ],
            [
                'name' => 'Paket & Barang Ekspedisi',
                'slug' => 'paket-barang-ekspedisi',
                'description' => 'Titip drop barang kiriman kurir, online shopping, atau logistik dagang siap ambil kapan saja.',
                'icon' => 'box-seam',
            ],
            [
                'name' => 'Helm & Perlengkapan Rider',
                'slug' => 'helm-perlengkapan-rider',
                'description' => 'Loker bersih untuk helm full face, jaket touring, dan sarung tangan anti bau di lokasi transit.',
                'icon' => 'shield-check',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 7. Facilities
        $facilitiesData = [
            ['name' => 'CCTV 24 Jam Nonstop', 'slug' => 'cctv-24-jam', 'icon' => 'camera-video'],
            ['name' => 'Ruang Ber-AC & Suhu Terjaga', 'slug' => 'ruang-ber-ac', 'icon' => 'snow'],
            ['name' => 'Proteksi Asuransi TitipSini', 'slug' => 'asuransi-titipsini', 'icon' => 'shield-fill-check'],
            ['name' => 'Petugas Keamanan Fisik (Satpam)', 'slug' => 'petugas-keamanan', 'icon' => 'person-badge'],
            ['name' => 'Barcode Tag & Segel Anti-Buka', 'slug' => 'barcode-segel', 'icon' => 'qr-code-scan'],
            ['name' => 'Bebas Banjir & Bebas Lembab', 'slug' => 'bebas-banjir', 'icon' => 'water'],
            ['name' => 'Bisa Drop & Go Kilat', 'slug' => 'drop-and-go', 'icon' => 'lightning-charge'],
        ];

        $facilities = [];
        foreach ($facilitiesData as $fac) {
            $facilities[$fac['slug']] = Facility::firstOrCreate(['slug' => $fac['slug']], $fac);
        }

        // 8. Listings
        $listing1 = Listing::firstOrCreate(
            ['slug' => 'loker-koper-eksekutif-stasiun-gambir'],
            [
                'vendor_profile_id' => $vendor1->id,
                'category_id' => $categories['koper-tas-wisata']->id,
                'title' => 'Loker Koper Eksekutif Stasiun Gambir',
                'tagline' => 'Titip koper nyaman tanpa repot, 2 menit jalan kaki dari peron Stasiun Gambir',
                'description' => 'Ideal bagi wisatawan dan profesional bisnis yang transit di Jakarta Pusat. Dilengkapi pengawasan CCTV 24 jam, ruang ber-AC bebas bau, serta asuransi barang bawaan resmi. Proses check-in dan serah terima serba digital via QR Code.',
                'address' => 'Jl. Kebon Sirih No. 45, Gambir (Seberang Pintu Barat Stasiun)',
                'city' => 'Jakarta Pusat',
                'district' => 'Gambir',
                'postal_code' => '10110',
                'latitude' => -6.1768,
                'longitude' => 106.8306,
                'price_per_hour' => 10000,
                'price_per_day' => 35000,
                'price_per_week' => 200000,
                'price_per_month' => 650000,
                'capacity_total' => 25,
                'capacity_available' => 21,
                'status' => ListingStatus::Published,
                'is_featured' => true,
                'featured_image' => 'https://images.unsplash.com/photo-1584985383910-a7a54fa9a7f8?auto=format&fit=crop&w=800&q=80',
                'rules' => "1. Dilarang menitipkan barang berbahaya (senjata tajam, bahan peledak, zat beracun).\n2. Dilarang menitipkan makanan basah yang mudah membusuk.\n3. Barang wajib disegel dengan barcode tag saat serah terima.",
                'operational_hours' => [
                    'monday' => ['open' => '06:00', 'close' => '23:00'],
                    'tuesday' => ['open' => '06:00', 'close' => '23:00'],
                    'wednesday' => ['open' => '06:00', 'close' => '23:00'],
                    'thursday' => ['open' => '06:00', 'close' => '23:00'],
                    'friday' => ['open' => '06:00', 'close' => '23:00'],
                    'saturday' => ['open' => '06:00', 'close' => '23:00'],
                    'sunday' => ['open' => '06:00', 'close' => '23:00'],
                ],
                'published_at' => now(),
            ]
        );

        $listing1->facilities()->sync([
            $facilities['cctv-24-jam']->id,
            $facilities['ruang-ber-ac']->id,
            $facilities['asuransi-titipsini']->id,
            $facilities['barcode-segel']->id,
            $facilities['drop-and-go']->id,
        ]);

        // Seed slots for listing 1
        for ($i = 1; $i <= 10; $i++) {
            StorageSlot::firstOrCreate(
                ['listing_id' => $listing1->id, 'slot_number' => sprintf('LKR-GMB-%02d', $i)],
                [
                    'size' => $i <= 4 ? 'medium' : ($i <= 8 ? 'large' : 'extra_large'),
                    'max_weight_kg' => 30.00,
                    'is_active' => true,
                    'is_occupied' => false,
                ]
            );
        }

        $listing2 = Listing::firstOrCreate(
            ['slug' => 'safevault-gadget-dokumen-sudirman'],
            [
                'vendor_profile_id' => $vendor2->id,
                'category_id' => $categories['dokumen-berkas-penting']->id,
                'title' => 'SafeVault Gadget & Dokumen Sudirman Central',
                'tagline' => 'Penyimpanan eksklusif dokumen penting, laptop, dan kamera di kawasan segitiga emas',
                'description' => 'Fasilitas vault modern berstandar institusi keuangan. Dilengkapi kontrol kelembapan presisi (humidity control), sistem proteksi kebakaran gas inert non-air, dan akses sekuriti berlapis. Sangat cocok bagi pebisnis, ekspatriat, dan pelamar visa.',
                'address' => 'Gedung Wisma Sentral Lt. GF, Jl. Jend. Sudirman Kav. 28',
                'city' => 'Jakarta Selatan',
                'district' => 'Setiabudi',
                'postal_code' => '12920',
                'latitude' => -6.2155,
                'longitude' => 106.8214,
                'price_per_hour' => 15000,
                'price_per_day' => 50000,
                'price_per_week' => 280000,
                'price_per_month' => 950000,
                'capacity_total' => 30,
                'capacity_available' => 28,
                'status' => ListingStatus::Published,
                'is_featured' => true,
                'featured_image' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80',
                'rules' => "1. Dokumen dan barang berharga wajib difoto saat serah terima.\n2. Verifikasi KTP/Identitas resmi wajib dilakukan oleh penyimpan.\n3. Akses pengambilan hanya menggunakan autentikasi kode booking dan OTP akun.",
                'operational_hours' => [
                    'monday' => ['open' => '07:30', 'close' => '21:00'],
                    'tuesday' => ['open' => '07:30', 'close' => '21:00'],
                    'wednesday' => ['open' => '07:30', 'close' => '21:00'],
                    'thursday' => ['open' => '07:30', 'close' => '21:00'],
                    'friday' => ['open' => '07:30', 'close' => '21:00'],
                    'saturday' => ['open' => '08:00', 'close' => '18:00'],
                    'sunday' => ['open' => '09:00', 'close' => '16:00'],
                ],
                'published_at' => now(),
            ]
        );

        $listing2->facilities()->sync([
            $facilities['cctv-24-jam']->id,
            $facilities['ruang-ber-ac']->id,
            $facilities['asuransi-titipsini']->id,
            $facilities['petugas-keamanan']->id,
            $facilities['bebas-banjir']->id,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            StorageSlot::firstOrCreate(
                ['listing_id' => $listing2->id, 'slot_number' => sprintf('VLT-SDR-%02d', $i)],
                [
                    'size' => $i <= 6 ? 'small' : 'medium',
                    'max_weight_kg' => 15.00,
                    'is_active' => true,
                    'is_occupied' => false,
                ]
            );
        }

        $listing3 = Listing::firstOrCreate(
            ['slug' => 'titip-helm-rider-apparel-blok-m'],
            [
                'vendor_profile_id' => $vendor2->id,
                'category_id' => $categories['helm-perlengkapan-rider']->id,
                'title' => 'Rider Locker & Apparel Blok M Hub',
                'tagline' => 'Simpan helm premium, jaket, dan tas saat hangout di kawasan Blok M',
                'description' => 'Khusus dirancang untuk pengendara sepeda motor dan komuter MRT Blok M. Loker bersih berpelindung bantalan busa anti gores, lengkap dengan pengering hembusan angin higienis (UV sanitization) agar helm tidak apek.',
                'address' => 'Jl. Panglima Polim Raya No. 18, Melawai (Dekat Stasiun MRT Blok M)',
                'city' => 'Jakarta Selatan',
                'district' => 'Kebayoran Baru',
                'postal_code' => '12160',
                'latitude' => -6.2443,
                'longitude' => 106.7978,
                'price_per_hour' => 5000,
                'price_per_day' => 20000,
                'price_per_week' => 100000,
                'price_per_month' => 320000,
                'capacity_total' => 20,
                'capacity_available' => 18,
                'status' => ListingStatus::Published,
                'is_featured' => false,
                'featured_image' => 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=800&q=80',
                'rules' => '1. Helm wajib dalam kondisi kering saat dimasukkan.\n2. Menggunakan gembok digital berkode unik dari aplikasi.',
                'operational_hours' => [
                    'monday' => ['open' => '07:00', 'close' => '23:30'],
                    'tuesday' => ['open' => '07:00', 'close' => '23:30'],
                    'wednesday' => ['open' => '07:00', 'close' => '23:30'],
                    'thursday' => ['open' => '07:00', 'close' => '23:30'],
                    'friday' => ['open' => '07:00', 'close' => '23:30'],
                    'saturday' => ['open' => '07:00', 'close' => '23:30'],
                    'sunday' => ['open' => '07:00', 'close' => '23:30'],
                ],
                'published_at' => now(),
            ]
        );

        $listing3->facilities()->sync([
            $facilities['cctv-24-jam']->id,
            $facilities['drop-and-go']->id,
            $facilities['barcode-segel']->id,
        ]);
    }
}
