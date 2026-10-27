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

        // 6. Categories (6 Layanan Resmi TitipSini)
        $categoriesData = [
            [
                'name' => 'Penitipan Traveling',
                'slug' => 'penitipan-traveling',
                'description' => 'Penitipan koper kabin, backpack carrier, dan tas travel liburan dekat stasiun & bandara.',
                'icon' => 'briefcase-fill',
            ],
            [
                'name' => 'Pindahan Kost/Kontrakan',
                'slug' => 'pindahan-kost-kontrakan',
                'description' => 'Jasa bantuan pindahan kost, angkut kasur, lemari, kardus, dan perabotan kamar kontrakan.',
                'icon' => 'truck',
            ],
            [
                'name' => 'Penitipan Kendaraan',
                'slug' => 'penitipan-kendaraan',
                'description' => 'Penitipan aman sepeda motor dan mobil di lahan terlindung beratap, CCTV 24 jam & asuransi.',
                'icon' => 'car-front-fill',
            ],
            [
                'name' => 'Penitipan Barang Kos',
                'slug' => 'penitipan-barang-kos',
                'description' => 'Simpan aman barang kos saat liburan semester atau pulang kampung tanpa bayar sewa kos penuh.',
                'icon' => 'boxes',
            ],
            [
                'name' => 'Jasa Security Rumah (Bangunan)',
                'slug' => 'jasa-security-rumah',
                'description' => 'Pengawasan, ronda fisik, dan inspeksi berkala rumah atau bangunan kosong saat ditinggal bepergian.',
                'icon' => 'shield-lock-fill',
            ],
            [
                'name' => 'Jasa Angkut Barang Anak KKN',
                'slug' => 'jasa-angkut-barang-anak-kkn',
                'description' => 'Layanan antar-jemput logistik dan perlengkapan pengabdian mahasiswa KKN dari kampus ke desa.',
                'icon' => 'people-fill',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $cat['is_active'] = true;
            $categories[$cat['slug']] = Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Deactivate old categories not in the 6 official services
        Category::whereNotIn('slug', array_column($categoriesData, 'slug'))->update(['is_active' => false]);

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
            ['slug' => 'loker-penitipan-traveling-stasiun-gambir'],
            [
                'vendor_profile_id' => $vendor1->id,
                'category_id' => $categories['penitipan-traveling']->id,
                'title' => 'Loker Penitipan Traveling Stasiun Gambir',
                'tagline' => 'Titip koper & backpack nyaman tanpa ribet, 2 menit jalan kaki dari peron Gambir',
                'description' => 'Ideal bagi wisatawan, backpacker, dan profesional bisnis yang transit di Jakarta Pusat. Dilengkapi pengawasan CCTV 24 jam, ruang ber-AC bebas bau, serta asuransi barang bawaan resmi. Proses check-in dan serah terima serba digital via QR Code.',
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
            ['slug' => 'penitipan-barang-kos-semester-tebet'],
            [
                'vendor_profile_id' => $vendor1->id,
                'category_id' => $categories['penitipan-barang-kos']->id,
                'title' => 'Gudang Titip Barang Kos Liburan Tebet Hub',
                'tagline' => 'Hemat biaya kos! Simpan kasur, lemari, kardus, dan kipas saat libur semester',
                'description' => 'Solusi hemat bagi mahasiswa dan perantau di kawasan Tebet & sekitarnya. Tidak perlu bayar sewa kos penuh berbulan-bulan saat libur kuliah atau mudik. Ruang bersih, bebas lembab, dan berpenjaga 24 jam.',
                'address' => 'Jl. Tebet Barat Raya No. 88, Tebet',
                'city' => 'Jakarta Selatan',
                'district' => 'Tebet',
                'postal_code' => '12810',
                'latitude' => -6.2345,
                'longitude' => 106.8532,
                'price_per_hour' => 8000,
                'price_per_day' => 25000,
                'price_per_week' => 120000,
                'price_per_month' => 350000,
                'capacity_total' => 40,
                'capacity_available' => 35,
                'status' => ListingStatus::Published,
                'is_featured' => true,
                'featured_image' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80',
                'rules' => "1. Barang dibungkus plastik/kardus dengan rapi.\n2. Dilengkapi label identitas dan segel barcode resmi TitipSini.\n3. Pengambilan wajib memperlihatkan kode booking aktif.",
                'operational_hours' => [
                    'monday' => ['open' => '08:00', 'close' => '21:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '21:00'],
                    'wednesday' => ['open' => '08:00', 'close' => '21:00'],
                    'thursday' => ['open' => '08:00', 'close' => '21:00'],
                    'friday' => ['open' => '08:00', 'close' => '21:00'],
                    'saturday' => ['open' => '08:00', 'close' => '20:00'],
                    'sunday' => ['open' => '09:00', 'close' => '18:00'],
                ],
                'published_at' => now(),
            ]
        );

        $listing2->facilities()->sync([
            $facilities['cctv-24-jam']->id,
            $facilities['asuransi-titipsini']->id,
            $facilities['petugas-keamanan']->id,
            $facilities['bebas-banjir']->id,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            StorageSlot::firstOrCreate(
                ['listing_id' => $listing2->id, 'slot_number' => sprintf('KOS-TBT-%02d', $i)],
                [
                    'size' => $i <= 5 ? 'medium' : 'large',
                    'max_weight_kg' => 25.00,
                    'is_active' => true,
                    'is_occupied' => false,
                ]
            );
        }

        $listing3 = Listing::firstOrCreate(
            ['slug' => 'penitipan-kendaraan-aman-sudirman-hub'],
            [
                'vendor_profile_id' => $vendor2->id,
                'category_id' => $categories['penitipan-kendaraan']->id,
                'title' => 'Penitipan Motor & Mobil Aman Sudirman Hub',
                'tagline' => 'Parkir inap terlindung kanopi, pengawasan satpam 24 jam & CCTV aktif',
                'description' => 'Layanan parkir inap dan penitipan kendaraan bermotor terpercaya untuk perjalanan dinas atau liburan panjang. Area beratap anti panas dan hujan, asuransi kendaraan, serta pencatatan odometer dan fisik kendaraan secara detail.',
                'address' => 'Jl. Jend. Sudirman Kav. 28, Setiabudi',
                'city' => 'Jakarta Selatan',
                'district' => 'Setiabudi',
                'postal_code' => '12920',
                'latitude' => -6.2155,
                'longitude' => 106.8214,
                'price_per_hour' => 5000,
                'price_per_day' => 20000,
                'price_per_week' => 110000,
                'price_per_month' => 380000,
                'capacity_total' => 30,
                'capacity_available' => 24,
                'status' => ListingStatus::Published,
                'is_featured' => true,
                'featured_image' => 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=800&q=80',
                'rules' => "1. Menyerahkan fotokopi STNK sah.\n2. Kunci kendaraan dititipkan di brankas berlabel segel.\n3. Dilakukan pemeriksaan kondisi fisik kendaraan bersama sebelum titip.",
                'operational_hours' => [
                    'monday' => ['open' => '06:00', 'close' => '23:30'],
                    'tuesday' => ['open' => '06:00', 'close' => '23:30'],
                    'wednesday' => ['open' => '06:00', 'close' => '23:30'],
                    'thursday' => ['open' => '06:00', 'close' => '23:30'],
                    'friday' => ['open' => '06:00', 'close' => '23:30'],
                    'saturday' => ['open' => '06:00', 'close' => '23:30'],
                    'sunday' => ['open' => '06:00', 'close' => '23:30'],
                ],
                'published_at' => now(),
            ]
        );

        $listing3->facilities()->sync([
            $facilities['cctv-24-jam']->id,
            $facilities['petugas-keamanan']->id,
            $facilities['asuransi-titipsini']->id,
            $facilities['bebas-banjir']->id,
        ]);

        $listing4 = Listing::firstOrCreate(
            ['slug' => 'jasa-pindahan-kost-kontrakan-express'],
            [
                'vendor_profile_id' => $vendor2->id,
                'category_id' => $categories['pindahan-kost-kontrakan']->id,
                'title' => 'Jasa Pindahan Kost & Kontrakan Armada Express',
                'tagline' => 'Pindahan tanpa capek! Armada pick-up bertutup terpal & tenaga angkut cekatan',
                'description' => 'Layanan lengkap pindahan kost, kontrakan, maupun apartemen. Meliputi bongkar muat barang besar (kasur springbed, lemari pakaian, kulkas mini, mesin cuci) dengan garansi keamanan dan pengikatan kuat.',
                'address' => 'Jl. Panglima Polim Raya No. 18, Kebayoran Baru',
                'city' => 'Jakarta Selatan',
                'district' => 'Kebayoran Baru',
                'postal_code' => '12160',
                'latitude' => -6.2443,
                'longitude' => 106.7978,
                'price_per_hour' => 25000,
                'price_per_day' => 150000,
                'price_per_week' => 800000,
                'price_per_month' => 2500000,
                'capacity_total' => 15,
                'capacity_available' => 12,
                'status' => ListingStatus::Published,
                'is_featured' => false,
                'featured_image' => 'https://images.unsplash.com/photo-1600518464441-9154a4dea21b?auto=format&fit=crop&w=800&q=80',
                'rules' => "1. Barang pecah belah wajib diberi tanda fragile.\n2. Jadwal angkut disepakati minimal 6 jam sebelum keberangkatan.",
                'operational_hours' => [
                    'monday' => ['open' => '07:00', 'close' => '21:00'],
                    'tuesday' => ['open' => '07:00', 'close' => '21:00'],
                    'wednesday' => ['open' => '07:00', 'close' => '21:00'],
                    'thursday' => ['open' => '07:00', 'close' => '21:00'],
                    'friday' => ['open' => '07:00', 'close' => '21:00'],
                    'saturday' => ['open' => '07:00', 'close' => '21:00'],
                    'sunday' => ['open' => '08:00', 'close' => '20:00'],
                ],
                'published_at' => now(),
            ]
        );

        $listing4->facilities()->sync([
            $facilities['asuransi-titipsini']->id,
            $facilities['drop-and-go']->id,
        ]);

        $listing5 = Listing::firstOrCreate(
            ['slug' => 'jasa-security-patroli-rumah-kosong-jakarta'],
            [
                'vendor_profile_id' => $vendor1->id,
                'category_id' => $categories['jasa-security-rumah']->id,
                'title' => 'Jasa Security & Patroli Rumah Kosong Profesional',
                'tagline' => 'Tinggalkan rumah dengan tenang saat liburan, mudik, atau renovasi',
                'description' => 'Layanan inspeksi dan pengamanan properti residensial. Petugas sekuriti bersertifikat melakukan patroli fisik berkala, pengecekan kunci gembok, lampu rumah, aliran listrik, serta laporan foto & video real-time via aplikasi.',
                'address' => 'Jl. Radio Dalam Raya No. 42, Gandaria',
                'city' => 'Jakarta Selatan',
                'district' => 'Kebayoran Baru',
                'postal_code' => '12140',
                'latitude' => -6.2551,
                'longitude' => 106.7912,
                'price_per_hour' => 15000,
                'price_per_day' => 75000,
                'price_per_week' => 450000,
                'price_per_month' => 1600000,
                'capacity_total' => 20,
                'capacity_available' => 18,
                'status' => ListingStatus::Published,
                'is_featured' => false,
                'featured_image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80',
                'rules' => "1. Laporan harian dikirim setiap pukul 09:00 dan 21:00 WIB.\n2. Verifikasi kepemilikan bangunan dilakukan sebelum masa kontrak patroli.",
                'operational_hours' => [
                    'monday' => ['open' => '00:00', 'close' => '23:59'],
                    'tuesday' => ['open' => '00:00', 'close' => '23:59'],
                    'wednesday' => ['open' => '00:00', 'close' => '23:59'],
                    'thursday' => ['open' => '00:00', 'close' => '23:59'],
                    'friday' => ['open' => '00:00', 'close' => '23:59'],
                    'saturday' => ['open' => '00:00', 'close' => '23:59'],
                    'sunday' => ['open' => '00:00', 'close' => '23:59'],
                ],
                'published_at' => now(),
            ]
        );

        $listing5->facilities()->sync([
            $facilities['petugas-keamanan']->id,
            $facilities['asuransi-titipsini']->id,
        ]);

        $listing6 = Listing::firstOrCreate(
            ['slug' => 'jasa-angkut-logistik-kkn-mahasiswa'],
            [
                'vendor_profile_id' => $vendor2->id,
                'category_id' => $categories['jasa-angkut-barang-anak-kkn']->id,
                'title' => 'Jasa Angkut Logistik & Barang KKN Mahasiswa',
                'tagline' => 'Solusi antar perlengkapan kelompok KKN dari kampus langsung ke desa pengabdian',
                'description' => 'Didesain khusus untuk kelompok mahasiswa KKN. Melayani angkut koper bersama, dispenser, galon, alat masak kelompok, banner, dan perlengkapan program kerja dengan tarif terjangkau dan muatan lega.',
                'address' => 'Jl. Kaliurang KM 5, Sleman (Kawasan Kampus)',
                'city' => 'Yogyakarta',
                'district' => 'Depok',
                'postal_code' => '55281',
                'latitude' => -7.7612,
                'longitude' => 110.3812,
                'price_per_hour' => 20000,
                'price_per_day' => 120000,
                'price_per_week' => 600000,
                'price_per_month' => 1800000,
                'capacity_total' => 25,
                'capacity_available' => 22,
                'status' => ListingStatus::Published,
                'is_featured' => true,
                'featured_image' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80',
                'rules' => "1. Koordinasi titik jemput kampus dan rute desa dilakukan via WhatsApp mitra.\n2. Maksimal muatan sesuai kapasitas armada yang dipesan.",
                'operational_hours' => [
                    'monday' => ['open' => '06:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '06:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '06:00', 'close' => '22:00'],
                    'thursday' => ['open' => '06:00', 'close' => '22:00'],
                    'friday' => ['open' => '06:00', 'close' => '22:00'],
                    'saturday' => ['open' => '06:00', 'close' => '22:00'],
                    'sunday' => ['open' => '06:00', 'close' => '22:00'],
                ],
                'published_at' => now(),
            ]
        );

        $listing6->facilities()->sync([
            $facilities['drop-and-go']->id,
            $facilities['asuransi-titipsini']->id,
        ]);
    }
}
