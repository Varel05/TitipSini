---
title: "PRD TitipSini — Migrasi dari WhatsApp ke Website Terintegrasi"
product: "TitipSini"
document_type: "Product Requirements Document"
version: "1.1"
date: "2026-10-27"
status: "Draft v1.1 — fokus migrasi proses WhatsApp ke website terintegrasi; perlu validasi bisnis"
language: "id-ID"
primary_audience: ["Product", "UI/UX", "Engineering", "QA", "Operations"]
source_file: "PRD_TitipSini.md"
---

# TitipSini — PRD Website Terintegrasi

**Deskripsi:** Platform layanan penitipan barang, kendaraan, dan properti dengan website sebagai pusat transaksi, data, status operasional, dan pelaporan. WhatsApp diposisikan sebagai kanal pendukung untuk notifikasi dan bantuan, bukan sumber data utama transaksi.

> **Catatan konteks proyek:** Tujuan pengembangan yang diberikan untuk PRD ini adalah memindahkan proses yang saat ini banyak dilakukan melalui WhatsApp ke website yang terintegrasi. Ini adalah konteks dari pemilik kebutuhan, bukan hasil pengamatan langsung terhadap percakapan atau sistem operasional internal. Website harus menjadi *single source of truth*; WhatsApp boleh tetap dipakai untuk notifikasi, pengingat, dan bantuan. Halaman live https://titipsini.com belum dapat diperiksa langsung pada audit awal, sehingga detail UI dan fitur aktif wajib divalidasi.

## Cara membaca dokumen ini

- **Temuan publik** adalah informasi yang didukung sumber sekunder dan bukan bukti mutlak fitur live saat ini.
- **Rekomendasi / usulan** adalah rancangan produk yang perlu disetujui Product Owner.
- **P0** berarti wajib untuk MVP; **P1** penting dan dapat menyusul; **P2** peningkatan setelah perilaku inti tervalidasi.
- Target metrik, SLA, angka performa, dan aturan transaksi di dokumen ini merupakan target awal sampai baseline dan kebijakan bisnis disahkan.
- **Prinsip migrasi kanal:** website menyimpan transaksi dan status resmi; WhatsApp hanya menjadi kanal komunikasi pendukung. Aksi penting dari WhatsApp harus mengarahkan pengguna kembali ke halaman website yang relevan.

## Daftar Isi

- [1. Ringkasan Eksekutif](#1-ringkasan-eksekutif)
- [2. Konteks Produk, Masalah, dan Sasaran](#2-konteks-produk-masalah-dan-sasaran)
- [2.5 Kondisi Saat Ini dan Kondisi Target](#25-kondisi-saat-ini-dan-kondisi-target)
- [3. Ruang Lingkup dan Prioritas](#3-ruang-lingkup-dan-prioritas)
- [4. Peran Pengguna dan Hak Akses](#4-peran-pengguna-dan-hak-akses)
- [5. Alur Pengguna Utama](#5-alur-pengguna-utama)
- [5.4 Alur Transisi WhatsApp ke Website](#54-alur-transisi-whatsapp-ke-website)
- [6. Kebutuhan Fungsional — Customer & Booking](#6-kebutuhan-fungsional--customer--booking)
- [6.2 Kebutuhan Peralihan Kanal dan Notifikasi](#62-kebutuhan-peralihan-kanal-dan-notifikasi)
- [7. Kebutuhan Fungsional — Vendor, Admin, Finance](#7-kebutuhan-fungsional--vendor-admin-finance)
- [8. Aturan Bisnis, Data, dan Integrasi](#8-aturan-bisnis-data-dan-integrasi)
- [8.4 Prinsip Integrasi WhatsApp dan Website](#84-prinsip-integrasi-whatsapp-dan-website)
- [9. Kebutuhan Nonfungsional, Keamanan, dan UX](#9-kebutuhan-nonfungsional-keamanan-dan-ux)
- [10. Analitik, Rencana Rilis, dan Risiko Produk](#10-analitik-rencana-rilis-dan-risiko-produk)
- [11. Pertanyaan Terbuka sebelum PRD menjadi Final](#11-pertanyaan-terbuka-sebelum-prd-menjadi-final)
- [12. Referensi dan Batasan Analisis](#12-referensi-dan-batasan-analisis)
- [Lampiran A — Definisi istilah](#lampiran-a--definisi-istilah)

## 1. Ringkasan Eksekutif

TitipSini diarahkan menjadi website operasional terintegrasi untuk layanan penitipan. Fokus pengembangan bukan hanya membuat website sebagai halaman informasi, melainkan memindahkan alur kerja yang sebelumnya banyak berlangsung melalui WhatsApp ke sistem yang terstruktur: katalog layanan, ketersediaan, booking, pembayaran/konfirmasi pembayaran, status penitipan, bukti serah-terima, dukungan, dashboard vendor, dashboard admin, dan laporan.

Masalah yang ingin diselesaikan adalah proses yang tersebar di percakapan: detail calon customer perlu ditanyakan berulang, ketersediaan sulit dilihat secara konsisten, status pesanan bergantung pada follow-up manual, bukti pembayaran atau serah-terima tersebar, dan rekap operasional memerlukan pekerjaan manual. Asumsi ini berasal dari arah proyek yang diberikan dan perlu dipetakan bersama tim operasional sebelum implementasi.

**Keputusan produk utama:** website menjadi *system of record* untuk semua data dan perubahan status. WhatsApp tetap dapat digunakan untuk akuisisi, notifikasi, pengingat, dan bantuan, tetapi bukan tempat resmi untuk membuat atau mengubah status transaksi. Jika permintaan masuk melalui WhatsApp selama masa transisi, staf mencatatnya melalui dashboard website agar tetap memiliki booking ID, status, dan riwayat yang sama.

### Hasil akhir yang dituju

1. Customer dapat menemukan layanan, memahami harga/aturan, membuat booking, membayar atau mengirim bukti sesuai metode yang disahkan, lalu memantau status dari satu halaman website.
2. Vendor mengelola listing, kapasitas, keputusan booking, bukti check-in/check-out, dan ringkasan pendapatan dari portal vendor.
3. Admin dan finance memproses verifikasi, kasus bermasalah, pembayaran, refund, dan payout dari dashboard dengan jejak audit.
4. WhatsApp terhubung melalui tautan dalam pesan, notifikasi status, atau bantuan; setiap pesan transaksional berasal dari data website dan tidak menjadi satu-satunya bukti transaksi.
5. Manajemen dapat melihat funnel, jumlah booking, penyebab pembatalan, SLA respons, dan performa vendor tanpa merekap percakapan secara manual.

### Temuan publik dan batas validasi

| Area | Temuan / konteks | Implikasi PRD |
| --- | --- | --- |
| Positioning | Metadata publik mengindikasikan layanan penitipan barang; dokumentasi sekunder menguraikan layanan barang, kendaraan, dan bangunan/properti. | Model kategori dibuat fleksibel dan detail layanan harus dikonfirmasi. |
| Konteks migrasi | Pemilik kebutuhan menyatakan tujuan pengembangan adalah beralih dari penggunaan WhatsApp ke website terintegrasi. | Alur digital end-to-end dan dashboard internal menjadi fokus; sumber informasi ini adalah konteks proyek, bukan audit operasional independen. |
| Aktor sistem | Dokumentasi terkait menyebut customer, vendor, admin, finance, dan superadmin. | Terapkan RBAC dan dashboard sesuai tanggung jawab. |
| Pencarian & transaksi | Dokumentasi sekunder menguraikan pencarian lokasi, pemesanan, status, pembayaran, dan payout. | Rancang lifecycle booking dan transaksi yang dapat ditelusuri. |
| Keterbatasan audit | Halaman live tidak berhasil dimuat langsung; UI dan alur aktif belum dapat diverifikasi. | Jangan menganggap rekomendasi di dokumen ini sebagai fitur yang sudah ada. |

### Prinsip desain yang tidak boleh dilanggar

- **One booking ID:** setiap permintaan yang masuk melalui website atau dibantu staf harus tercatat sebagai satu booking dengan ID unik.
- **Website first:** perubahan resmi pada booking, pembayaran, check-in/out, pembatalan, dan refund dilakukan melalui website/API.
- **Channel-independent:** booking tidak boleh gagal hanya karena WhatsApp/email tidak terkirim; notifikasi dapat dicoba ulang tanpa menggandakan transaksi.
- **Auditability:** aksi manusia maupun otomatis menyimpan actor, waktu, sumber kanal, alasan, dan perubahan status.
- **Gradual migration:** selama pilot, admin masih boleh memasukkan booking yang datang dari WhatsApp. Setelah adopsi stabil, komunikasi WhatsApp diarahkan agar customer membuat atau mengelola booking melalui website.

### Keputusan produk yang disarankan

- Bangun pengalaman customer dan vendor yang mobile-first; dashboard operasional admin/finance dioptimalkan untuk desktop.
- Prioritaskan proses booking dan operasional dasar yang benar-benar menggantikan pekerjaan manual sebelum menambahkan fitur promosi atau aplikasi native.
- Integrasi WhatsApp dilakukan setelah aturan notifikasi, template, persetujuan pengguna, batasan provider, dan biaya disepakati.
- Pisahkan status booking, status pembayaran, status penitipan, dan status notifikasi; jangan mencampur semuanya dalam satu status.

## 2. Konteks Produk, Masalah, dan Sasaran

### 2.1 Masalah pengguna yang hendak diselesaikan

| Masalah proses yang perlu divalidasi | Dampak saat proses bergantung pada chat | Kebutuhan pada website terintegrasi |
| --- | --- | --- |
| Informasi layanan tersebar di percakapan. | Customer harus bertanya berulang tentang lokasi, tarif, kapasitas, jam operasional, dan aturan. | Katalog, halaman detail vendor, FAQ, tarif, dan kebijakan yang selalu diperbarui. |
| Pencatatan permintaan tidak terstruktur. | Detail customer/barang/periode berisiko terlewat atau ditulis ulang. | Form booking tervalidasi, booking ID, dan data terstruktur. |
| Ketersediaan tidak terlihat secara konsisten. | Vendor/admin perlu mengecek chat atau catatan manual; risiko bentrok kapasitas meningkat. | Availability dan kapasitas yang dikelola dari dashboard dan divalidasi server. |
| Status pesanan bergantung pada follow-up. | Customer perlu menanyakan apakah pesanan diterima, dibayar, dititipkan, atau selesai. | Halaman detail booking dengan timeline status dan notifikasi otomatis. |
| Bukti transaksi tersebar. | Bukti pembayaran, foto kondisi barang, dan bukti serah-terima sulit dicari ulang. | Dokumen/bukti tersimpan pada record booking dengan kontrol akses. |
| Pekerjaan admin sulit diukur. | Rekap booking, sumber lead, pembatalan, dan performa vendor memerlukan pekerjaan manual. | Dashboard, laporan, event analytics, dan sumber kanal (`website`, `whatsapp_assisted`, `admin_created`). |
| Customer mengharapkan respons cepat. | Pesan yang masuk di luar jam operasional dapat tertunda dan mudah terlewat. | Form/booking yang dapat diproses kapan saja, status ekspektasi waktu respons, dan antrean kerja. |

### 2.2 Sasaran bisnis

- Memindahkan pencatatan dan pengelolaan transaksi dari percakapan individual ke website yang terintegrasi.
- Mengurangi waktu staf untuk menjawab pertanyaan berulang, menyalin data, mengecek status, dan membuat laporan manual.
- Meningkatkan keterlacakan dan kualitas data dari inquiry hingga booking selesai.
- Mengurangi risiko booking ganda, data hilang, status tidak sinkron, serta kesalahan rekonsiliasi.
- Membuat pengalaman customer lebih mandiri tanpa menghilangkan kanal bantuan manusia.
- Menyediakan fondasi untuk bertambahnya vendor, lokasi, dan jenis layanan.

### 2.3 Sasaran pengguna (proposed)

| Persona | Job-to-be-done | Indikator pengalaman baik |
| --- | --- | --- |
| Customer | Menemukan layanan yang sesuai dan menyelesaikan booking tanpa harus menunggu percakapan manual untuk setiap langkah. | Dapat melihat harga/aturan, mengirim data satu kali, memiliki nomor booking, dan melacak status sendiri. |
| Vendor | Mengelola listing, ketersediaan, dan semua booking dalam satu portal. | Tidak perlu mencari detail transaksi di banyak chat; tindakan operasional tercatat. |
| Admin operasional | Menangani verifikasi, booking dari semua kanal, dan kasus pengecualian. | Punya antrean terpusat, pencarian berdasarkan ID, serta riwayat lengkap. |
| Finance | Merekonsiliasi pembayaran, refund, dan payout. | Tiap transaksi berhubungan dengan booking ID dan referensi provider. |
| Manajemen | Memantau permintaan, konversi, SLA, dan beban operasional. | Laporan dapat dilihat dari dashboard dan tidak perlu menggabungkan spreadsheet secara manual. |

### 2.4 Metrik keberhasilan awal

Baseline belum tersedia. Metrik dan target di bawah adalah usulan untuk pilot; ukur kondisi awal terlebih dahulu lalu sepakati target final.

| Metrik | Definisi | Target awal yang diusulkan |
| --- | --- | --- |
| Website booking share | Booking baru yang dibuat customer di website / semua booking baru. | Naik setiap minggu setelah rollout; target persentase final ditetapkan setelah baseline. |
| WhatsApp-assisted capture rate | Booking yang bermula dari WhatsApp tetapi dicatat lengkap di website / seluruh booking dari WhatsApp. | 100% sejak proses transisi dimulai. |
| Website status visibility | Booking aktif yang memiliki status/timeline valid di website / seluruh booking aktif. | 100%. |
| Manual follow-up rate | Booking yang memerlukan customer menghubungi admin hanya untuk menanyakan status / seluruh booking aktif. | Turun dari baseline setelah status dan notifikasi berjalan. |
| Median admin handling time | Waktu staf untuk mencatat dan memproses satu booking. | Turun dari baseline yang diukur pada fase discovery. |
| Booking conversion | Booking terkonfirmasi / sesi yang melihat detail listing. | Tetapkan baseline per kategori/lokasi; jangan gunakan satu angka untuk semua kategori tanpa data. |
| Payment success rate | Pembayaran sukses / percobaan pembayaran. | ≥ 95% jika menggunakan payment gateway, tidak termasuk penolakan oleh bank/pelanggan bila pelaporan provider memisahkannya. |
| Completion rate | Booking selesai / booking terkonfirmasi. | ≥ 90% sebagai target awal yang perlu dikaji berdasarkan kebijakan layanan. |

### 2.5 Kondisi Saat Ini dan Kondisi Target

Kondisi saat ini berikut adalah hipotesis kerja berdasarkan tujuan migrasi yang disampaikan dan wajib dikonfirmasi melalui workshop operasional.

| Area | Kondisi saat ini yang diasumsikan | Kondisi target setelah integrasi |
| --- | --- | --- |
| Inquiry | Customer menghubungi WhatsApp dan admin menjawab pertanyaan. | Informasi standar tersedia di website; WhatsApp menjadi kanal bantuan dan mengarahkan ke halaman yang tepat. |
| Input pesanan | Detail diambil melalui chat dan mungkin dicatat manual. | Customer mengisi form; admin dapat membuat booking berbantuan untuk lead dari WhatsApp. |
| Ketersediaan | Dicek secara manual melalui vendor/chat/catatan. | Ketersediaan dikelola di dashboard dan dicek server sebelum booking disahkan. |
| Konfirmasi | Dikirim lewat chat secara manual. | Booking memiliki ID serta status; pesan hanya memberi notifikasi dan tautan ke detail booking. |
| Pembayaran | Metode dan bukti berpotensi tersebar di chat. | Payment record, reference, bukti atau callback, serta status rekonsiliasi tersimpan pada booking. Metode final perlu disahkan. |
| Operasional | Update penitipan dan serah-terima disampaikan lewat chat. | Vendor mengubah status dan mengunggah bukti dari portal; customer melihat timeline. |
| Laporan | Riwayat perlu ditelusuri atau direkap manual. | Dashboard/report dengan filter tanggal, vendor, status, dan kanal asal. |

**Prinsip migrasi data:** tidak perlu mengimpor seluruh histori chat WhatsApp. Migrasikan hanya data yang dibutuhkan untuk booking aktif, vendor, listing, saldo/transaksi terbuka, dan riwayat yang memiliki tujuan bisnis yang jelas. Untuk permintaan aktif yang belum memiliki record, admin memasukkannya lewat dashboard; impor massal CSV hanya dilakukan jika struktur data dan otorisasi sudah disepakati.

## 3. Ruang Lingkup dan Prioritas

### 3.1 In scope — MVP / P0

| Modul | Cakupan P0 | Hasil minimum yang harus bekerja |
| --- | --- | --- |
| Website publik & katalog | Landing page, kategori, halaman listing/vendor, FAQ, kebijakan, CTA booking, responsif untuk mobile. | Customer bisa memahami layanan tanpa harus bertanya melalui WhatsApp untuk informasi dasar. |
| Akun & profil | Registrasi/login, reset akses, profil customer/vendor dan status verifikasi sesuai aturan. | Identitas dan data transaksi terikat pada akun; akses hanya ke data yang berhak. |
| Pencarian & detail | Lokasi, kategori, periode, kapasitas, harga, fasilitas, aturan, dan ketersediaan. | Customer dapat membandingkan opsi dan mengetahui persyaratan sebelum memesan. |
| Booking & availability | Form booking, detail barang, periode, total biaya, booking ID, status dan riwayat. | Booking tervalidasi di server dan tidak bergantung pada percakapan WhatsApp. |
| Vendor portal | Kelola listing, kapasitas, booking, keputusan menerima/menolak, check-in/out, bukti kondisi. | Vendor memproses booking dari dashboard dan semua tindakan tercatat. |
| Admin dashboard | Antrean booking, verifikasi, listing, tiket, booking yang dibantu staf, dan pencarian ID. | Admin dapat menangani pengecualian tanpa menyimpan status resmi hanya di chat/spreadsheet. |
| Booking berbantuan admin | Buat booking atas nama customer yang masuk lewat WhatsApp/telepon selama masa transisi; simpan `source_channel` dan actor. | Semua booking aktif masuk ke sistem dengan ID, owner, status, dan audit trail yang sama. |
| Pembayaran & finance | Integrasi payment gateway jika disepakati; jika belum, alur pembayaran manual dengan bukti/reference dan rekonsiliasi terkontrol. | Sumber kebenaran status pembayaran tetap database website, bukan isi chat atau screenshot tanpa record. |
| Timeline & notifikasi | Halaman status booking, notifikasi in-app/email dan/atau WhatsApp untuk perubahan penting. | Customer/vendor menerima update; kegagalan pengiriman notifikasi tidak menggagalkan transaksi. |
| Dukungan | Form bantuan/tiket yang terhubung dengan booking ID; tautan WhatsApp dapat tersedia untuk eskalasi. | Keluhan memiliki nomor tiket dan status, walaupun percakapan bantuan berlangsung di WhatsApp. |
| Keamanan & audit | RBAC, TLS, log tindakan, penyimpanan file privat, validasi data, backup. | Data dan perubahan status dapat ditelusuri serta dilindungi. |
| Analytics dasar | Sumber kanal, funnel booking, waktu pemrosesan, pembatalan, dan status notifikasi. | Tim dapat mengukur peralihan dari WhatsApp ke website. |

### 3.2 Out of scope pada MVP / kandidat fase berikutnya

| Fitur | Alasan / prasyarat |
| --- | --- |
| Aplikasi native iOS/Android | Validasi pengalaman web responsif lebih dahulu. |
| Impor otomatis seluruh histori chat WhatsApp | Tidak dibutuhkan untuk sumber data resmi; rumit dari sisi API, privasi, format, dan kualitas data. Fokus pada booking aktif dan data operasional yang relevan. |
| Chat real-time penuh di dalam website | Bisa ditambahkan setelah kebutuhan komunikasi terbukti; MVP menggunakan timeline, notifikasi, tiket, dan tautan bantuan. |
| AI chatbot untuk menjawab semua permintaan | Bangun basis informasi dan alur standar dahulu; otomasi AI perlu guardrail dan pemantauan. |
| Dynamic pricing dan optimasi kapasitas berbasis AI | Memerlukan volume transaksi dan data yang memadai. |
| Live tracking pickup/drop-off | Memerlukan model dispatch dan tanggung jawab logistik yang jelas. |
| Multi-gateway/ERP tingkat lanjut | Tambahkan jika volume dan kebutuhan finance mengharuskan. |
| Program loyalti/voucher kompleks | Prioritas setelah alur booking utama dan adopsi website stabil. |

### 3.3 Dependensi utama

- Pemetaan proses WhatsApp yang dipakai saat ini: pertanyaan awal, pencatatan data, pengecekan vendor, konfirmasi, pembayaran, serah-terima, komplain, dan laporan.
- Keputusan metode pembayaran, refund, komisi, payout, SLA vendor, dan kebijakan pembatalan.
- Pemilik data dan sumber data awal: listing, vendor, booking aktif, pembayaran tertunda, serta permintaan yang belum selesai.
- Pilihan kanal notifikasi: WhatsApp Business Platform/API atau provider resmi, email, dan/atau SMS; verifikasi biaya, template, consent, batas pengiriman, dan ketersediaan API.
- SOP siapa yang memasukkan booking dari WhatsApp selama transisi dan siapa yang memastikan data lengkap.
- Penyedia hosting, storage berkas privat, payment gateway, dan map/geocoding jika dibutuhkan.

## 4. Peran Pengguna dan Hak Akses

Rekomendasi berikut mempertahankan lima role utama dan menerapkan hak akses sebagai permission yang divalidasi di backend. Semua role harus bekerja pada data terpusat; percakapan WhatsApp tidak menjadi pengganti catatan resmi booking.

| Role | Tanggung jawab | Akses utama | Batas akses |
| --- | --- | --- | --- |
| Customer | Mencari dan menggunakan layanan penitipan. | Pencarian/detail, booking sendiri, pembayaran, status, permintaan pengambilan, review, tiket bantuan, profil. | Tidak melihat booking atau data identitas customer lain; tidak mengubah status operasional vendor. |
| Vendor | Mengelola lokasi/layanan dan proses penitipan. | Listing milik sendiri, kapasitas, harga, jam operasional, booking yang ditugaskan, bukti check-in/out, laporan, payout. | Hanya mengakses data booking yang berelasi dengan vendor/lokasi sendiri. |
| Admin | Operasional dan trust & safety. | Verifikasi KYC, review listing/lokasi, akun customer/vendor, booking, komplain, moderasi, laporan operasional. | Tidak melakukan payout atau mengubah saldo secara langsung kecuali diberi permission khusus dan audit log. |
| Finance | Keuangan dan rekonsiliasi. | Pembayaran, transaksi, refund, komisi, payout vendor, laporan keuangan. | Tidak mengakses isi dokumen identitas secara luas; hanya data minimum yang diperlukan. |
| Superadmin | Konfigurasi dan kontrol menyeluruh. | Pengguna internal, role/permission, kategori, tarif/komisi, konfigurasi sistem, dashboard global, audit log. | Aksi berisiko tinggi wajib tercatat; dukung prinsip least privilege dan persetujuan ganda jika dibutuhkan. |

### 4.1 Pemisahan wewenang minimum

- Admin memverifikasi identitas/lokasi; finance memvalidasi transaksi dan payout; superadmin mengatur role serta parameter global.
- Refund atau payout di atas ambang nominal bisnis dapat memerlukan maker-checker (dibuat oleh satu petugas, disetujui petugas lain). Ambang nominal harus ditentukan pemilik produk/finance.
- Akses dokumen identitas dibatasi berdasarkan kebutuhan tugas; setiap akses dan perubahan status verifikasi dicatat.

## 5. Alur Pengguna Utama

### 5.1 Customer — alur utama melalui website

1. Customer membuka website, memilih kategori/lokasi/periode, lalu melihat listing yang cocok.
2. Customer membuka detail listing dan membaca tarif, kapasitas, fasilitas, aturan barang, jam operasional, kebijakan pembatalan, serta opsi pickup bila tersedia.
3. Customer mengisi detail barang dan periode melalui form booking. Sistem menampilkan rincian biaya sebelum konfirmasi.
4. Customer login/registrasi dan menyelesaikan verifikasi yang diwajibkan.
5. Sistem memvalidasi ketersediaan, membuat booking ID, dan menampilkan cara pembayaran serta batas waktu berikutnya.
6. Setelah pembayaran terverifikasi atau direkonsiliasi sesuai metode yang disepakati, booking bergerak ke status berikutnya dan vendor mendapat notifikasi.
7. Customer memantau timeline booking dari website; notifikasi email/WhatsApp hanya berisi ringkasan dan tautan aman untuk membuka detailnya.
8. Vendor mencatat check-in, kondisi barang, dan bukti serah-terima. Customer melihat status terbaru dari akun.
9. Customer mengajukan pengambilan bila diperlukan; vendor mencatat check-out dan customer mengonfirmasi penyelesaian.
10. Customer memberi ulasan atau membuat tiket bantuan yang terhubung dengan booking.

### 5.2 Vendor — alur melalui portal vendor

1. Vendor mendaftar, melengkapi profil dan data lokasi, lalu mengajukan verifikasi.
2. Admin meninjau vendor/lokasi; vendor memperbaiki data jika diminta.
3. Vendor membuat listing, tarif, kapasitas, jadwal, fasilitas, aturan, serta foto.
4. Setelah listing disetujui, vendor mengelola slot/kapasitas dari dashboard.
5. Booking baru muncul pada antrean portal vendor beserta tenggat respons dan detail yang diperlukan.
6. Vendor menerima/menolak booking dari website. Penolakan harus disertai alasan; sistem mengurus notifikasi dan kebijakan refund jika relevan.
7. Vendor mencatat check-in dan check-out dengan foto/checklist serta timestamp.
8. Vendor melihat rekap pendapatan, saldo, payout, dan riwayat transaksi dari portal.

### 5.3 Admin/Finance — alur terpusat

- Admin bekerja dari satu antrean untuk verifikasi, review listing, booking baru, permintaan berbantuan WhatsApp, tiket bantuan, serta booking terlambat atau bermasalah.
- Booking yang dibuat oleh staf wajib menyimpan sumber kanal, staf pembuat, waktu pembuatan, dan siapa customer/vendor terkait.
- Finance merekonsiliasi pembayaran/refund/payout berdasarkan record dan referensi transaksi, tidak hanya berdasarkan screenshot atau pesan yang diteruskan.
- Jika masalah masuk melalui WhatsApp, admin membuat/menautkan tiket ke booking di website dan mencatat ringkasan serta hasil tindak lanjut, tanpa memindahkan percakapan pribadi yang tidak relevan.

### 5.4 Alur Transisi WhatsApp ke Website

1. **Lead baru dari WhatsApp:** staf membagikan tautan kategori/listing atau form booking yang sesuai. Jika customer tidak dapat mengisi sendiri, staf membuat booking berbantuan di dashboard.
2. **Pencatatan resmi:** website menghasilkan booking ID dan menyimpan `source_channel = whatsapp_assisted` untuk booking yang dibuat atau dibantu dari WhatsApp.
3. **Konfirmasi dan pembayaran:** website menampilkan status dan instruksi yang resmi. Pesan WhatsApp hanya menginformasikan perubahan dan mengarahkan ke halaman booking.
4. **Proses vendor:** vendor menerima pekerjaan dari dashboard, bukan hanya dari pesan personal. Jika vendor merespons admin melalui WhatsApp selama masa pilot, admin tetap memperbarui status melalui website.
5. **Penitipan dan pengambilan:** bukti, waktu, dan perubahan status dicatat pada booking di website; customer mendapatkan tautan ke timeline.
6. **Bantuan/sengketa:** kontak WhatsApp tetap boleh dipakai untuk komunikasi manusia, tetapi tiket, keputusan, dan hasil penyelesaian dicatat pada website.
7. **Pengurangan ketergantungan:** ukur porsi booking mandiri dan assisted setiap minggu. Kurangi pembuatan booking melalui chat hanya setelah funnel website, kesiapan vendor, dan dukungan sudah memadai.

**Aturan penting:** jangan menjanjikan bahwa WhatsApp akan tersinkron otomatis dengan semua percakapan. Sinkronisasi yang didukung pada MVP adalah notifikasi dan tautan, serta pencatatan booking oleh admin; integrasi API dua arah harus diputuskan dan diuji secara terpisah.

## 6. Kebutuhan Fungsional — Customer & Booking

Prioritas: P0 = wajib untuk MVP; P1 = penting tetapi dapat menyusul; P2 = peningkatan setelah perilaku inti tervalidasi.

| ID / Prioritas | Kebutuhan | Kriteria penerimaan (acceptance criteria) |
| --- | --- | --- |
| CUS-01 / P0 | Registrasi, login, logout, reset kata sandi. | Email/nomor telepon yang valid dan unik dapat mendaftar; pesan error jelas; reset menggunakan token/OTP yang kedaluwarsa; sesi dapat dicabut. |
| CUS-02 / P0 | Profil dan verifikasi identitas. | Customer dapat melihat status KYC; pengunggahan tervalidasi tipe/ukuran; status pending/approved/rejected/revision terlihat; booking yang dibatasi tidak dapat dilanjutkan sebelum approved. |
| CUS-03 / P0 | Pencarian dan filter listing. | Filter kategori, lokasi, periode, kisaran harga, dan kapasitas menghasilkan daftar sesuai kriteria; empty state memberi opsi mengubah filter. |
| CUS-04 / P0 | Detail listing dan vendor. | Menampilkan harga, kapasitas/ketersediaan, fasilitas, aturan, jam operasional, lokasi, status verifikasi, foto, dan kebijakan pembatalan sebelum booking. |
| CUS-05 / P0 | Kalkulasi harga transparan. | Subtotal dan semua biaya tambahan ditampilkan sebelum konfirmasi; total di server dihitung ulang; total tidak berubah diam-diam setelah customer menyetujui. |
| CUS-06 / P0 | Buat booking. | Booking memiliki ID unik dan ringkasan; validasi availability dilakukan di server; double-submit/retry tidak membuat booking duplikat. |
| CUS-07 / P0 | Pembayaran dan konfirmasi status. | Status payment berasal dari callback terverifikasi atau review internal yang tercatat; refresh halaman tidak menggandakan transaksi. |
| CUS-08 / P0 | Pantau status penitipan. | Customer melihat status, timestamp, lokasi/vendor, dan riwayat penting dari booking miliknya sendiri. |
| CUS-09 / P0 | Permintaan pengambilan/pengembalian. | Customer dapat mengajukan waktu pengambilan; vendor mendapat notifikasi; permintaan hanya tersedia pada status yang relevan. |
| CUS-10 / P0 | Bantuan dan sengketa. | Customer dapat membuka tiket dengan kategori, deskripsi, serta lampiran yang dibatasi; sistem menampilkan nomor tiket dan status tindak lanjut. |
| CUS-11 / P1 | Rating dan ulasan. | Hanya customer dengan booking selesai yang dapat memberi satu ulasan per booking; moderasi spam/penyalahgunaan tersedia. |
| CUS-12 / P1 | Favorit dan perbandingan listing. | Customer dapat menyimpan listing dan membandingkan parameter utama, tanpa mengubah flow booking inti. |

### 6.1 Kebutuhan status booking

Gunakan state machine yang tervalidasi oleh backend. UI hanya menawarkan aksi yang sah pada status saat ini; perubahan status wajib menyimpan actor, timestamp, dan alasan bila ada.

| Status utama | Makna | Aksi yang diizinkan (contoh) |
| --- | --- | --- |
| awaiting_payment | Booking dibuat, belum lunas. | Bayar; batalkan sesuai kebijakan; kedaluwarsa otomatis. |
| payment_review | Pembayaran menunggu verifikasi/rekonsiliasi. | Finance/admin review; customer melihat status menunggu. |
| awaiting_vendor | Pembayaran valid; keputusan vendor dibutuhkan. | Vendor terima/tolak; sistem timeout mengikuti SLA. |
| confirmed | Vendor menerima dan kapasitas dipesan. | Customer/vendor mempersiapkan penyerahan. |
| checked_in / stored | Barang sudah diterima dan sedang dititipkan. | Lihat bukti; minta pengambilan sesuai kebijakan. |
| retrieval_requested / ready_for_return | Pengambilan diminta atau siap dilayani. | Konfirmasi jadwal; vendor check-out. |
| completed | Serah-terima selesai dikonfirmasi. | Review; laporan masalah dalam tenggat kebijakan. |
| cancelled / refund_pending / refunded | Booking dibatalkan dan status refund. | Lihat alasan, referensi, dan status pengembalian dana. |
| disputed | Ada sengketa yang sedang ditangani. | Unggah bukti, lihat status; payout dapat ditahan sesuai policy. |


### 6.2 Kebutuhan peralihan kanal dan notifikasi

| ID / Prioritas | Kebutuhan | Kriteria penerimaan |
| --- | --- | --- |
| CHN-01 / P0 | Simpan sumber kanal booking. | Setiap booking menyimpan `source_channel` seperti `website`, `whatsapp_assisted`, `phone_assisted`, atau `admin_created`; nilainya tidak dapat diubah tanpa permission dan audit log. |
| CHN-02 / P0 | Admin membuat booking berbantuan. | Admin dapat memasukkan data minimum customer, listing, periode, harga, catatan, dan kanal asal; sistem membuat booking ID dan timeline seperti booking website. |
| CHN-03 / P0 | Notifikasi berbasis event website. | Perubahan status penting menghasilkan notifikasi dari event sistem; isi pesan menggunakan data booking terbaru dan mencantumkan tautan ke halaman yang memerlukan autentikasi jika datanya sensitif. |
| CHN-04 / P0 | Riwayat pengiriman notifikasi. | Sistem menyimpan kanal, penerima, template, waktu, status provider, jumlah retry, dan alasan gagal tanpa menyimpan informasi sensitif yang tidak perlu. |
| CHN-05 / P0 | Kegagalan kanal tidak merusak transaksi. | Jika WhatsApp/email gagal, booking tetap memiliki status yang benar; sistem mencoba ulang sesuai kebijakan dan menyediakan fallback pada dashboard. |
| CHN-06 / P0 | Update booking hanya melalui website. | Customer/vendor tidak dapat mengubah status resmi hanya dengan mengirim pesan WhatsApp; tindakan harus dilakukan lewat website atau dicatat admin dengan actor dan alasan. |
| CHN-07 / P1 | Template dan preferensi kanal. | Admin yang berwenang mengelola template; sistem menghormati consent/preferensi kanal dan aturan provider. |
| CHN-08 / P1 | Link konteks ke detail booking/tiket. | Pesan notifikasi membuka halaman web yang relevan; token tautan tidak memberikan akses berlebihan dan kedaluwarsa sesuai kebijakan. |
| CHN-09 / P1 | Dashboard adopsi kanal. | Admin/manajemen dapat melihat booking website, assisted WhatsApp, kegagalan notifikasi, dan tren adopsi per periode. |

## 7. Kebutuhan Fungsional — Vendor, Admin, Finance

### 7.1 Vendor portal

| ID / Prioritas | Kebutuhan | Kriteria penerimaan |
| --- | --- | --- |
| VEN-01 / P0 | Onboarding vendor dan data lokasi. | Vendor mengisi profil, kontak, alamat, dokumen identitas, dan bukti lokasi; status verifikasi tampil dan perubahan penting masuk audit log. |
| VEN-02 / P0 | Kelola listing/layanan. | Vendor dapat membuat draft; listing baru tidak tampil publik sebelum persetujuan jika moderation diaktifkan; edit signifikan dapat memicu review ulang. |
| VEN-03 / P0 | Kapasitas dan ketersediaan. | Kapasitas tidak boleh negatif; booking tidak boleh melebihi kapasitas; perubahan ketersediaan tidak merusak booking terkonfirmasi. |
| VEN-04 / P0 | Terima/tolak booking. | Vendor melihat rincian yang diperlukan; penolakan wajib beralasan; customer diberi notifikasi; timeout ditangani oleh sistem. |
| VEN-05 / P0 | Bukti check-in/check-out. | Vendor dapat merekam waktu, kondisi, catatan, dan foto; bukti tertaut ke booking dengan akses terbatas. |
| VEN-06 / P0 | Rekening dan payout. | Perubahan rekening memerlukan verifikasi ulang; vendor dapat melihat saldo tersedia, tertahan, dan riwayat payout. |
| VEN-07 / P1 | Laporan dan performa. | Vendor melihat jumlah booking, completion, pembatalan, rating, dan pendapatan dalam rentang tanggal. |

### 7.2 Admin operasional

| ID / Prioritas | Kebutuhan | Kriteria penerimaan |
| --- | --- | --- |
| ADM-01 / P0 | Verifikasi customer/vendor/lokasi. | Antrean dapat difilter; reviewer menyetujui/menolak/meminta perbaikan dengan alasan; tindakan dicatat. |
| ADM-02 / P0 | Moderasi listing. | Admin dapat publish, reject, suspend, atau meminta revisi; alasan ditampilkan kepada vendor. |
| ADM-03 / P0 | Pengelolaan booking dan dukungan. | Admin dapat mencari dengan ID booking/customer/vendor; setiap perubahan administratif memiliki reason code dan actor. |
| ADM-04 / P0 | Kategori dan kebijakan layanan. | Kategori, barang terlarang, atribut wajib, dan template aturan dikelola dari dashboard atau konfigurasi terkontrol. |
| ADM-05 / P1 | Dashboard operasional. | Menampilkan antrean KYC, booking bermasalah, tiket terbuka, listing pending, dan SLA yang hampir terlewati. |

### 7.3 Finance

| ID / Prioritas | Kebutuhan | Kriteria penerimaan |
| --- | --- | --- |
| FIN-01 / P0 | Rekonsiliasi pembayaran. | Transaksi menyimpan gross amount, fee, net amount, provider reference, timestamp, serta status yang konsisten. |
| FIN-02 / P0 | Refund. | Refund memiliki alasan, nominal, referensi provider, status, dan audit trail; refund parsial hanya jika policy mengizinkan. |
| FIN-03 / P0 | Payout vendor. | Payout memeriksa saldo tersedia, rekening terverifikasi, hold sengketa, dan duplikasi request; status sukses/gagal tercatat. |
| FIN-04 / P0 | Ledger dan laporan. | Perubahan saldo direpresentasikan sebagai entri ledger immutable; laporan dapat difilter per tanggal, vendor, status, dan ID transaksi. |
| FIN-05 / P1 | Approval berlapis. | Transaksi di atas ambang konfigurasi memerlukan approval petugas lain; pembuat tidak dapat menyetujui permintaan sendiri. |

### 7.4 Superadmin dan konfigurasi

- Mengelola role/permission, kategori, parameter tarif/komisi, batas minimum/maksimum, SLA vendor, aturan kedaluwarsa, dan template notifikasi.
- Membuka dashboard platform dan audit log; ekspor data sensitif memerlukan permission khusus dan dicatat.
- Menyediakan feature flags atau konfigurasi untuk mengaktifkan kategori, pickup, metode pembayaran, dan wilayah layanan tanpa deployment kode untuk perubahan rutin.

## 8. Aturan Bisnis, Data, dan Integrasi

### 8.1 Aturan bisnis yang direkomendasikan

| ID | Aturan | Catatan validasi |
| --- | --- | --- |
| BR-01 | Customer hanya dapat booking jika akun memenuhi status verifikasi dan persyaratan data kategori. | Ketentuan ini selaras dengan dokumentasi kebutuhan publik; jenis verifikasi final perlu disepakati. |
| BR-02 | Vendor hanya dapat mempublikasikan listing setelah identitas dan lokasi disetujui. | Status lokasi terpisah dari status akun vendor. |
| BR-03 | Availability dan kapasitas dikunci secara atomik saat booking dibuat/checkout dimulai. | TTL hold ditentukan produk; usulan awal 10–15 menit selama pembayaran berlangsung. |
| BR-04 | Semua harga dihitung di server dan disimpan sebagai snapshot pada booking. | Perubahan tarif listing tidak mengubah harga transaksi yang sudah disetujui. |
| BR-05 | Status booking, payment, refund, payout, dan storage merupakan state terpisah. | Hindari satu enum campuran untuk semua lifecycle. |
| BR-06 | Transisi status hanya boleh melalui aksi yang berizin dan valid. | Backend memvalidasi previous state, target state, role, dan kondisi prasyarat. |
| BR-07 | Barang yang dilarang atau membutuhkan penanganan khusus harus ditampilkan sebelum checkout. | Menyediakan konfigurasi kategori/atribut dan kebijakan penolakan. |
| BR-08 | Pickup bersifat opsional dan hanya tersedia jika vendor mendukung area/jadwalnya. | Tarif, tanggung jawab barang, serta bukti serah-terima harus jelas sebelum pesan. |
| BR-09 | Payout hanya berasal dari saldo yang sudah earned, tidak sedang ditahan, dan belum dicairkan. | Perhitungan berdasar ledger; jangan mengandalkan angka saldo yang dapat diedit manual. |
| BR-10 | Semua tindakan finansial dan perubahan verifikasi harus mempunyai audit trail. | Log append-only; koreksi menggunakan entri penyesuaian, bukan menghapus riwayat. |

### 8.2 Entitas data utama

| Entitas | Isi inti | Relasi penting |
| --- | --- | --- |
| User / Role / Permission | Identitas akun, login, status akun, role. | User punya satu atau lebih role/permission sesuai kebijakan. |
| IdentityVerification | Status KYC, metadata dokumen, reviewer, alasan, timestamps. | Milik user; akses ke file sensitif dibatasi. |
| Vendor / Site | Profil vendor, lokasi, alamat, koordinat opsional, jam operasional, status verifikasi. | Vendor dapat memiliki beberapa lokasi. |
| Listing / Category / Facility | Jenis layanan, deskripsi, fasilitas, aturan, tarif, foto, status publikasi. | Listing terkait satu site dan kategori; atribut dapat dikonfigurasi per kategori. |
| Availability / Capacity | Slot, kapasitas, blackout date, jumlah terpakai/tertahan. | Dipakai untuk mencegah overbooking dan menghitung ketersediaan. |
| Booking / BookingItem | Periode, customer, vendor/site/listing, detail barang, harga snapshot, status. | Memiliki riwayat status dan payment references. |
| HandoverEvidence | Foto kondisi, checklist, waktu, actor, catatan check-in/out. | Terhubung ke booking dan memiliki kontrol akses. |
| Payment / Refund / Ledger / Payout | Nominal, fee, provider reference, status, entries debit/kredit. | Ledger mencatat setiap mutasi; payment/refund/payout idempotent. |
| Notification / SupportTicket / Review | Pesan, status pengiriman, tiket, bukti, rating/ulasan. | Terkait user, booking, atau vendor sesuai konteks. |
| AuditLog | Actor, action, entity, before/after, reason, timestamp, request ID. | Mencakup aksi sensitif; tidak dapat diedit oleh role operasional biasa. |

### 8.3 Integrasi eksternal

| Integrasi | Tujuan | Kebutuhan desain |
| --- | --- | --- |
| Payment gateway | Pembayaran dan refund. | Verifikasi signature callback, idempotency key, retry, rekonsiliasi dan sandbox. |
| WhatsApp Business Platform/API, email, atau SMS provider | OTP (jika dipakai), notifikasi status, pengingat, dan tautan kontekstual. | Website menjadi sumber kebenaran; gunakan provider resmi, template yang disetujui bila diwajibkan, consent/preferensi, retry, idempotency, serta status pengiriman. Notifikasi bukan satu-satunya bukti transaksi. |
| Peta/geocoding (opsional P0/P1) | Cari lokasi, tampilkan jarak, validasi area pickup. | Minta izin lokasi; tetap sediakan input manual; jangan membuat fitur bergantung mutlak pada geolocation. |
| Penyimpanan berkas | Dokumen KYC, foto listing, bukti kondisi. | URL privat/signed, batas ukuran/tipe, pemindaian file bila memungkinkan, masa retensi, akses tercatat. |

### 8.4 Prinsip Integrasi WhatsApp dan Website

Integrasi harus mengurangi kerja manual tanpa membuat bisnis bergantung pada keberhasilan pengiriman pesan. Untuk MVP, utamakan pola **website event → notification service → provider WhatsApp/email**; integrasi percakapan dua arah bukan prasyarat.

| Prinsip | Persyaratan |
| --- | --- |
| Sumber kebenaran | Booking, payment, refund, storage status, ticket, dan payout tersimpan di database website. Pesan WhatsApp tidak mengubah record secara langsung. |
| Event-driven | Event seperti `booking_created`, `payment_confirmed`, `vendor_accepted`, `checked_in`, `retrieval_requested`, `completed`, `cancelled`, dan `refund_updated` dapat memicu notifikasi. |
| Tautan kontekstual | Pesan mengarahkan ke halaman booking/tiket yang tepat; halaman tetap menerapkan autentikasi dan authorization. Hindari memasukkan KTP, alamat sensitif, atau detail pembayaran lengkap ke pesan. |
| Idempotency dan retry | Event yang dikirim ulang tidak boleh menggandakan booking/pembayaran atau mengirim notifikasi berulang tanpa kontrol. Gunakan event ID, retry policy, dan deduplication. |
| Observability | Dashboard memperlihatkan status antrean dan provider: queued, sent/accepted, delivered bila provider mendukung, failed, retrying. Bedakan `sent` dari `delivered`. |
| Batas API | Jangan mengasumsikan WhatsApp Business App dapat diintegrasikan seperti API. Pemilihan WhatsApp Business Platform/provider harus divalidasi terhadap kebutuhan, biaya, template, opt-in, dan kebijakan terbaru. |
| Fallback | Bila provider tidak tersedia, transaksi tetap berjalan; admin dapat melihat kegagalan dan memakai kanal alternatif sesuai SOP. |
| Privasi | Simpan nomor kontak dan log delivery secukupnya; batasi akses; tetapkan retensi; jangan mengimpor seluruh riwayat chat tanpa kebutuhan, otorisasi, dan dasar pemrosesan yang sah. |

**Kontrak data minimum untuk notifikasi:** `notification_id`, `event_id`, `booking_id` atau `ticket_id`, `recipient_user_id`, `channel`, `template_key`, `provider_message_id`, `delivery_status`, `attempt_count`, `created_at`, `last_attempt_at`, dan `failure_code` bila ada.

**Bukan cakupan MVP:** membaca seluruh percakapan WhatsApp, menyelaraskan semua pesan dua arah otomatis, atau menjadikan balasan bebas sebagai instruksi untuk mengubah pembayaran/status booking. Fitur tersebut baru dipertimbangkan jika ada kebutuhan bisnis jelas, rancangan consent, validasi keamanan, dan pengujian idempotency.

## 9. Kebutuhan Nonfungsional, Keamanan, dan UX

| ID | Area | Persyaratan yang dapat diuji |
| --- | --- | --- |
| NFR-01 | Performa | P95 untuk API baca umum ≤ 500 ms dan API pencarian ≤ 1,5 detik pada beban pilot yang disepakati; ukur dari server dan pantau error rate. |
| NFR-02 | Ketersediaan | Target awal ≥ 99,5% bulanan saat pilot, tidak termasuk maintenance terjadwal yang diumumkan. |
| NFR-03 | Keamanan transport | HTTPS/TLS valid wajib pada seluruh halaman dan API; redirect HTTP ke HTTPS; cookie Secure, HttpOnly, dan SameSite sesuai skenario. |
| NFR-04 | Kontrol akses | RBAC ditegakkan di backend untuk setiap endpoint dan object; pengujian memastikan user A tidak dapat membaca booking/user B dengan mengganti ID. |
| NFR-05 | Data pribadi | Kumpulkan data seperlunya; batasi akses KYC; tetapkan retensi, penghapusan, dan prosedur insiden yang konsisten dengan regulasi yang berlaku. |
| NFR-06 | Pembayaran | Callback provider diverifikasi; operasi finansial idempotent; rekonsiliasi berkala; tidak menyimpan data kartu sensitif jika provider meng-host proses kartu. |
| NFR-07 | Reliabilitas | Backup terjadwal; uji restore; target awal RPO ≤ 24 jam dan RTO ≤ 8 jam untuk MVP, lalu ditinjau berdasarkan risiko. |
| NFR-08 | Auditability | Perubahan role, KYC, listing, booking status, fee, refund, dan payout menyimpan actor, waktu, alasan, serta correlation ID. |
| NFR-09 | Aksesibilitas | Navigasi keyboard, label input, kontras memadai, status tidak hanya ditandai warna, dan error message mengarah pada aksi perbaikan. |
| NFR-10 | Responsif | Customer/vendor flow inti berfungsi pada viewport mobile 360 px ke atas dan desktop; tabel operasional memiliki alternatif tampilan mobile yang layak. |
| NFR-11 | Observability | Log terstruktur, monitoring error, latency, job queue, payment callback, notifikasi gagal, dan alert transaksi bermasalah. |
| NFR-12 | Kompatibilitas | Mendukung dua versi mayor terbaru browser utama yang disepakati produk; graceful fallback untuk fitur browser yang tidak didukung. |
| NFR-13 | Reliabilitas notifikasi | Pengiriman via queue/retry; deduplikasi event; kegagalan provider terlihat di dashboard dan tidak mengubah status transaksi. |
| NFR-14 | Integrasi eksternal | Secret provider disimpan di secret manager/environment; webhook diverifikasi; request/provider callbacks dicatat tanpa mengekspos token atau data pribadi. |

### 9.1 Risiko keamanan prioritas

> **Prioritas tinggi — validasi sertifikat dan HTTPS**
> Profil pemindai pihak ketiga yang diperiksa pada 8 Oktober 2026 menandai kegagalan verifikasi SSL dan HTTP tidak terenkripsi. Karena hasil ini otomatis dan halaman tidak dapat dimuat dalam pemeriksaan ini, anggap sebagai temuan yang perlu diverifikasi, bukan kesimpulan final. Sebelum launch, QA/DevOps harus mengecek sertifikat, chain, hostname, redirect HTTPS, HSTS, cookie, dan semua domain/API terkait.

- Jangan menaruh file KTP/dokumen identitas pada bucket publik atau URL yang bisa ditebak.
- Rate-limit login, OTP, reset password, upload, pencarian mahal, dan endpoint pembayaran.
- Gunakan CSRF protection untuk sesi berbasis cookie, sanitasi input, validasi file upload, proteksi XSS/SQL injection, dan secret management.
- Pisahkan environment staging/production, gunakan test keys untuk sandbox, dan redaksi data pribadi dari log.

### 9.2 Prinsip UX dan konten

- Bahasa utama Indonesia, istilah sederhana, biaya dan aturan ditampilkan sebelum checkout, serta status transaksi memakai kata yang konsisten.
- Tampilkan empty, loading, partial failure, dan retry states; jangan hanya menunjukkan spinner tanpa penjelasan.
- Status booking dan aksi berikutnya harus terlihat di website; notifikasi WhatsApp/email menjadi pengingat, bukan satu-satunya tempat informasi penting disampaikan.
- Konfirmasi dua tahap untuk aksi berisiko: pembatalan setelah bayar, penolakan booking, persetujuan refund, dan payout.
- Bukti foto dan detail alamat hanya ditampilkan kepada pihak yang berhak dan pada tahap yang tepat demi privasi lokasi penyimpanan.

## 10. Analitik, Rencana Rilis, dan Risiko Produk

### 10.1 Event analytics minimum

| Event | Properti minimum | Tujuan analitik |
| --- | --- | --- |
| `search_submitted` | category, area, period, filters, result_count | Mengukur permintaan dan pencarian tanpa hasil. |
| `listing_viewed` | listing_id, category, vendor_id, price_snapshot | Mengukur daya tarik listing. |
| `booking_started` | listing_id, period, category, estimate_total, source_channel | Mengukur funnel dan kanal asal. |
| `booking_created` | booking_id, total, source_channel, created_by_role, vendor_id | Mengukur adopsi website dan penggunaan booking berbantuan. |
| `admin_assisted_booking_created` | booking_id, source_channel, staff_id, missing_fields_count | Memahami beban input manual tanpa mengirim PII ke analytics. |
| `payment_succeeded/failed` | booking_id, provider, amount, failure_code | Monitoring pembayaran dan rekonsiliasi. |
| `vendor_decision` | booking_id, accepted/rejected, response_time, reason_code | Menilai SLA vendor. |
| `handover_recorded` | booking_id, checkin/checkout, evidence_count | Memantau pemenuhan layanan. |
| `notification_queued/sent/failed` | event_id, channel, template_key, attempt_count, failure_code | Memantau integrasi WhatsApp/email dan fallback. |
| `booking_completed/cancelled/disputed` | booking_id, reason_code, lifecycle_duration, source_channel | Mengukur mutu operasional. |
| `support_ticket_created/resolved` | ticket_id, booking_id, origin_channel, resolution_time | Mengukur bantuan dan apakah masalah perlu dipindahkan ke sistem. |
| `verification_submitted/decision` | actor_type, status, processing_duration | Mengukur funnel dan SLA verifikasi. |

Jangan kirim nama lengkap, nomor identitas, nomor WhatsApp, alamat lengkap, foto dokumen, atau isi chat ke analytics umum. Gunakan ID internal/pseudonim dan batasi retensi.

### 10.2 Rencana rilis — migrasi bertahap

| Tahap | Fokus | Exit criteria |
| --- | --- | --- |
| R0 — Discovery & process mapping | Petakan alur WhatsApp aktual; identifikasi spreadsheet, form, pembayaran, dan sumber data; validasi aturan dan kebijakan. | Diagram as-is/to-be disetujui; pemilik proses, data yang dimigrasi, metode pembayaran, dan template notifikasi ditetapkan. |
| R1 — Foundation | Katalog, akun, listing/vendor, booking ID, dashboard admin/vendor, audit log, dan booking berbantuan. | Booking dari website dan input admin masuk ke database dan mengikuti lifecycle yang sama. |
| R2 — End-to-end pilot | Pembayaran/rekonsiliasi, status booking, check-in/out, tiket bantuan, notifikasi WhatsApp/email, analytics dasar. | Uji E2E lulus; status transaksi tetap benar ketika provider notifikasi gagal; data aktif tidak lagi tersebar sebagai sumber resmi. |
| R3 — Website-first rollout | Arahkan link dari WhatsApp ke website, edukasi vendor/customer, monitor adopsi, serta siapkan fallback operasional. | Porsi booking website naik; semua booking assisted tercatat; vendor mampu memproses booking di dashboard. |
| R4 — Optimasi dan kurangi manual entry | Perbaiki funnel, pencarian, automation, laporan dan SOP; kurangi pembuatan booking oleh admin sesuai bukti adopsi. | Metrik adopsi dan operasional stabil selama periode yang disepakati; tidak ada proses kritis yang hanya diketahui lewat chat. |

### 10.3 Risiko dan mitigasi

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Tim tetap memakai chat sebagai catatan resmi | Website tidak menjadi sumber kebenaran dan data kembali terpecah. | SOP dan permission yang jelas; semua booking assisted harus direkam; audit sampel dan metrik capture rate. |
| Vendor enggan memakai dashboard | Status dan kapasitas tetap diperbarui manual. | Onboarding, antarmuka mobile-friendly, pengingat, pelatihan, dan masa transisi bertahap. |
| Customer terus memilih WhatsApp | Adopsi self-service rendah. | Website menampilkan manfaat nyata, CTA spesifik, tautan langsung ke listing/booking, dan bantuan human-assisted saat perlu. |
| Notifikasi gagal atau terlambat | Customer tidak mengetahui perubahan status. | Queue, retry, dashboard kegagalan, kanal fallback, dan status selalu tersedia di website. |
| Booking ganda dari chat dan web | Kapasitas konflik dan proses refund meningkat. | Cari/cek customer dan periode sebelum membuat booking assisted; idempotency dan pemeriksaan availability di server. |
| Data lama tidak lengkap | Migrasi menghasilkan record tidak akurat. | Migrasikan hanya data yang diperlukan; validasi field wajib, sampling, rekonsiliasi, dan penanggung jawab data. |
| Informasi sensitif tersebar di pesan | Risiko privasi. | Template pesan minimal, tautan ke halaman terlindungi, edukasi staf, dan kebijakan retensi. |
| Pembayaran dan payout tidak cocok | Kerugian finansial dan hilangnya kepercayaan. | Immutable ledger, idempotency, rekonsiliasi harian, approval berlapis sesuai batas. |
| Pasokan vendor belum cukup | Pencarian tanpa hasil dan konversi rendah. | Mulai di area/kategori terbatas, onboarding supply sebelum kampanye demand, pantau pencarian tanpa hasil. |

## 11. Pertanyaan Terbuka sebelum PRD menjadi Final

Keputusan berikut diperlukan sebelum engineering mengunci implementasi. Pertanyaan ini tidak mengubah prinsip bahwa website menjadi sumber data transaksi resmi.

| ID | Pertanyaan untuk pemilik produk | Mengapa penting / keputusan yang diperlukan |
| --- | --- | --- |
| Q-01 | Proses apa saja yang saat ini dilakukan lewat WhatsApp dari awal hingga selesai? | Buat peta as-is yang akurat dan pastikan tidak ada pekerjaan inti yang terlewat. |
| Q-02 | Siapa yang menjawab WhatsApp, membuat catatan booking, mengecek vendor, dan menangani komplain? | Menetapkan ownership, permission, antrean kerja, dan SOP masa transisi. |
| Q-03 | Apakah website baru dibangun dari nol atau website/backend saat ini akan dikembangkan? | Menentukan arsitektur, migrasi, dan ruang lingkup perubahan. |
| Q-04 | Data mana yang wajib dipindah: vendor, listing, booking aktif, transaksi terbuka, atau histori? | Menentukan format impor, kualitas data, privasi, dan biaya migrasi. |
| Q-05 | Metode pembayaran saat ini dan metode target apa yang digunakan? | Menentukan payment gateway, bukti pembayaran, rekonsiliasi, status, dan risiko transaksi. |
| Q-06 | Apakah WhatsApp hanya untuk notifikasi, juga untuk support, atau akan dibuat integrasi dua arah? | Menentukan kebutuhan WhatsApp Business Platform/API, biaya, template, dan kompleksitas. Rekomendasi MVP: notifikasi satu arah dan link kontekstual dahulu. |
| Q-07 | Apakah vendor akan memakai dashboard sendiri sejak MVP atau masih dibantu admin? | Menentukan rollout, fitur minimum, training, dan masa coexistence. |
| Q-08 | Booking perlu konfirmasi vendor dahulu atau otomatis bila kapasitas tersedia? | Menentukan state machine, timeout, kapasitas, dan refund. |
| Q-09 | Berapa SLA respons vendor/admin dan siapa backup di luar jam operasional? | Menentukan notifikasi, eskalasi, jam layanan, dan status yang ditampilkan kepada customer. |
| Q-10 | Bagaimana aturan pembatalan/refund, komisi, settlement, payout, dan biaya gateway? | Harus eksplisit sebelum checkout dan dashboard finance dirilis. |
| Q-11 | Apa data identitas yang wajib, siapa yang dapat melihatnya, dan berapa lama disimpan? | Menentukan verifikasi, minimasi data, keamanan, dan retensi. |
| Q-12 | Kapan dan berdasarkan metrik apa proses booking melalui chat mulai dibatasi? | Menghindari cutover terlalu dini; kriteria harus mencakup adopsi customer/vendor dan stabilitas web. |
| Q-13 | Wilayah dan kategori mana yang menjadi pilot pertama? | Menjaga kompleksitas terkontrol dan memudahkan bantuan operasional. |
| Q-14 | Siapa pemilik keputusan sengketa dan kapan payout boleh ditahan? | Menentukan permission, maker-checker, dan alur eskalasi. |

## 12. Referensi dan Batasan Analisis

Dokumen revisi ini disusun pada 27 Oktober 2026. Halaman titipsini.com tidak berhasil dimuat langsung pada audit awal; susunan menu, tampilan terkini, harga, dan alur checkout belum diuji langsung. Fokus migrasi dari WhatsApp ke website terintegrasi berasal dari tujuan pengembangan yang disampaikan pemilik kebutuhan. Detail proses WhatsApp saat ini belum diamati, sehingga kondisi as-is dalam PRD adalah hipotesis kerja yang harus divalidasi melalui workshop bersama tim operasional. Informasi produk publik di bawah memanfaatkan metadata dan dokumentasi sekunder; semua rekomendasi perlu disetujui product owner.

| Sumber | Informasi yang digunakan | Catatan |
| --- | --- | --- |
| TitipSini — https://titipsini.com | URL target analisis. | Akses langsung gagal dimuat pada audit awal; UI live belum dapat diverifikasi. |
| Konteks pemilik kebutuhan | Arah pengembangan adalah memindahkan proses dari WhatsApp ke website yang terintegrasi. | Menjadi dasar sasaran migrasi di PRD ini; alur operasional aktual tetap perlu dipetakan dan dikonfirmasi. |
| Gridinsoft, profil Titipsini.com — https://es.gridinsoft.com/online-virus-scanner/url/titipsini-com | Judul halaman dan deskripsi yang tercatat oleh pemindai; catatan teknis SSL dari pemindai otomatis. | Halaman menyebut pemeriksaan tanggal 8 Oktober 2026. Temuan TLS perlu diuji ulang langsung karena hasil automated scanner dapat keliru. |
| “Spesifikasi Kebutuhan Software TitipSini” — https://id.scribd.com/document/933608229/SKPL-Website-Titipsini-com | Deskripsi marketplace penitipan; peran superadmin/admin/finance/vendor/customer; pencarian, booking, status, pembayaran, payout, dan batasan role. | Dokumen pihak ketiga/akademik; gunakan sebagai referensi sekunder, bukan bukti implementasi sistem yang saat ini aktif. |

### Lampiran A — Definisi istilah

| Istilah | Definisi dalam PRD |
| --- | --- |
| Listing | Penawaran jasa penitipan yang memiliki kategori, lokasi, kapasitas, tarif, fasilitas, dan aturan. |
| Booking | Permintaan/transaksi customer untuk satu layanan dan periode tertentu. |
| KYC | Proses verifikasi identitas pengguna/vendor sesuai kebijakan yang disepakati. |
| Check-in / Check-out | Bukti proses penerimaan barang dan pengembalian/serah-terima akhir. |
| Ledger | Catatan finansial debit/kredit yang menjadi jejak sumber kebenaran untuk saldo dan rekonsiliasi. |
| RBAC | Role-Based Access Control: izin ditentukan oleh role/permission dan divalidasi server. |
| System of record | Sistem utama yang menjadi sumber data resmi untuk transaksi dan status. Dalam PRD ini, website/database adalah system of record. |
| Booking berbantuan (assisted booking) | Booking yang dibuat staf di dashboard atas nama customer yang menghubungi melalui WhatsApp, telepon, atau kanal lain. |
| Source channel | Atribut yang menunjukkan asal permintaan/booking, misalnya website atau WhatsApp-assisted. |
| Event-driven notification | Pola ketika perubahan status pada website memicu pesan notifikasi melalui provider eksternal. |
