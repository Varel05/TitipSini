---
title: "ADR-002: Payment Gateway dan Settlement Vendor"
project: "TitipSini"
adr_id: "ADR-002"
status: "PROPOSED — alur pembayaran saat penutupan layanan telah ditentukan; detail implementasi Xendit perlu dibuktikan"
version: "1.2"
date: "2026-10-27"
owners:
  - Product Owner
  - Engineering Lead
  - Finance / Operations
---

# ADR-002: Payment Gateway dan Settlement Vendor

> **Status:** `PROPOSED`. Keputusan bisnis yang sudah ditetapkan: Xendit adalah provider prioritas; customer **tidak membayar pada saat booking**; pembayaran dilakukan ketika customer ingin menyelesaikan layanan dan mengambil barang; setelah pembayaran terkonfirmasi dan serah-terima selesai, payout vendor dipicu otomatis; komisi TitipSini 20% dari biaya penitipan; biaya gateway ditanggung customer; rekonsiliasi oleh Super Admin; WhatsApp tidak digunakan. Implementasi produksi masih mensyaratkan proof of concept dan konfirmasi Xendit atas model sub-account, split, biaya, serta payout ke rekening vendor.

## 1. Ringkasan keputusan

TitipSini menggunakan Xendit sebagai payment gateway prioritas, dengan **xenPlatform** sebagai kandidat utama untuk diuji karena TitipSini merupakan marketplace yang perlu mengelola vendor individu, komisi platform, dan penyaluran dana ke rekening vendor.

Pembayaran tidak dikumpulkan ketika customer membuat booking. Alur finansial dimulai saat customer ingin mengakhiri layanan penitipan dan mengambil barang:

1. Customer membuat booking tanpa pembayaran.
2. Vendor mengonfirmasi booking.
3. Barang dititipkan dan layanan mulai berjalan.
4. Saat customer meminta pengambilan barang, sistem menentukan jumlah biaya akhir dan membuat tagihan.
5. Customer membayar tagihan melalui checkout Xendit.
6. Setelah pembayaran diverifikasi oleh server, vendor menyerahkan barang dan customer/vendor mengonfirmasi serah-terima.
7. Setelah layanan ditandai selesai, sistem memicu penyaluran bagian vendor ke rekening bank vendor dan mencatat komisi TitipSini.
8. Super Admin merekonsiliasi pembayaran, biaya gateway, komisi, payout, serta laporan provider.

**Penting:** payment sukses, layanan selesai, split dana, payout ke rekening bank, dan settlement bank adalah kejadian berbeda. Sistem harus menyimpan status masing-masing secara terpisah. “Langsung dicairkan” berarti instruksi payout dibuat otomatis setelah kondisi bisnis terpenuhi; waktu dana masuk ke rekening tetap mengikuti kemampuan, jadwal, verifikasi, dan proses bank/provider.

## 2. Konteks dan keputusan bisnis

Keputusan yang diberikan Product Owner:

| Area | Keputusan |
|---|---|
| Provider prioritas | Xendit; xenPlatform adalah kandidat produk utama, belum keputusan produksi final. |
| Waktu pembayaran customer | Saat customer ingin menyelesaikan layanan dan mengambil kembali barang, bukan saat booking. |
| Penyaluran vendor | Ditargetkan ke rekening bank vendor segera setelah layanan selesai dan pembayaran berhasil. |
| Komisi TitipSini | 20% dari biaya penitipan, tidak dihitung dari biaya gateway. |
| Biaya payment gateway | Ditanggung customer dan harus terlihat pada rincian tagihan sebelum customer membayar, sepanjang skema ini diizinkan provider. |
| Vendor | Individu; persyaratan pendaftaran TitipSini mencakup SKCK yang harus diverifikasi dengan tata kelola data yang memadai. |
| Pembatalan | Customer boleh membatalkan sebelum barang dititipkan. Karena belum ada pembayaran, pembatalan pra-layanan normalnya tidak memerlukan refund. |
| WhatsApp | Tidak digunakan dalam sistem maupun alur operasional. |
| Rekonsiliasi | Tanggung jawab Super Admin. |
| Volume transaksi | Belum diketahui; ditentukan melalui pilot dan diperiksa kembali terhadap batas serta biaya provider. |
| Stack aplikasi | Laravel/PHP; adapter pembayaran harus dipisahkan dari domain booking dan layanan. |

## 3. Masalah yang ingin diselesaikan

TitipSini membutuhkan alur pembayaran berbasis layanan yang benar-benar terjadi, karena jumlah akhir dapat bergantung pada durasi penitipan dan biaya yang disepakati. Mengumpulkan uang pada saat booking tidak sesuai dengan keputusan bisnis saat ini.

Sistem harus:

- Mengizinkan booking, konfirmasi vendor, dan dimulainya penitipan tanpa membuat tagihan yang harus dibayar.
- Menghitung biaya akhir dengan aturan harga yang transparan ketika customer meminta barangnya kembali.
- Mengunci total tagihan sebelum checkout agar customer dan vendor memahami jumlah yang harus dibayar.
- Membuktikan status pembayaran melalui webhook/status inquiry provider, bukan berdasarkan redirect browser atau screenshot.
- Mencegah barang diserahkan sebelum pembayaran sukses, kecuali ada prosedur pengecualian yang diotorisasi dan diaudit.
- Memicu payout vendor setelah serah-terima dan penyelesaian layanan terkonfirmasi.
- Menyimpan jejak audit yang dapat direkonsiliasi oleh Super Admin.

## 4. Tujuan

1. Menjadikan Xendit provider prioritas untuk proof of concept integrasi.
2. Tidak mewajibkan pembayaran pada saat booking.
3. Membuat tagihan akhir saat customer meminta pengambilan barang dan layanan akan ditutup.
4. Menghitung komisi TitipSini sebesar 20% dari biaya penitipan secara server-side.
5. Menerapkan biaya gateway kepada customer secara transparan, jika diizinkan oleh provider dan ketentuan yang berlaku.
6. Menyalurkan bagian vendor ke rekening bank vendor setelah pembayaran berhasil dan layanan selesai, melalui mekanisme yang didukung Xendit.
7. Menghindari pembayaran atau payout ganda melalui idempotensi dan kontrol status.
8. Memberi Super Admin informasi untuk memantau payment, komisi, fee, payout, kegagalan, dan rekonsiliasi.
9. Memungkinkan pergantian provider melalui adapter, tanpa menyebarkan kode spesifik Xendit ke seluruh aplikasi.

## 5. Bukan tujuan dan batasan

- ADR ini tidak menyatakan bahwa split langsung ke rekening bank dapat terjadi secara sinkron pada saat customer membayar. Xendit harus mengonfirmasi arsitektur yang tepat: payment atas nama sub-account, split ke saldo sub-account, transfer saldo, payout/disbursement ke rekening, atau kombinasi yang disetujui.
- Dana **tidak direncanakan untuk dikumpulkan pada saat booking atau ditahan selama layanan berlangsung**. Tagihan dibuat saat customer meminta pengambilan barang.
- “Payout segera” adalah instruksi otomatis yang dipicu setelah layanan selesai; bukan jaminan dana tiba instan.
- Tidak ada integrasi WhatsApp.
- Metode pembayaran spesifik (misalnya VA, QRIS, e-wallet, kartu) belum ditetapkan; pilih berdasarkan kemampuan Xendit, biaya, pengalaman customer, dan hasil pilot.
- Biaya gateway yang ditagihkan terpisah kepada customer harus dikonfirmasi. Jangan menambahkan surcharge atau biaya yang tidak diizinkan provider atau ketentuan yang berlaku.
- Pembatalan sebelum barang dititipkan normalnya tidak memiliki transaksi pembayaran untuk dikembalikan. Ini tidak menghapus kebutuhan menangani payment yang tidak sengaja tercipta, pembayaran ganda, sengketa, atau koreksi transaksi.
- Apabila layanan sudah dimulai dan customer meminta pengambilan, sistem harus menghitung biaya yang telah terakumulasi sesuai aturan harga. Kebijakan khusus untuk pengambilan lebih awal, sengketa biaya, dan layanan parsial perlu disepakati sebelum produksi.
- Dokumen SKCK merupakan data berisiko tinggi. Tujuan dan dasar pemrosesan, akses, penyimpanan, retensi, penghapusan, serta audit harus ditetapkan dan ditinjau secara tepat sebelum fitur aktif.
- Biaya, eligibility vendor individu, onboarding sub-account, batas transaksi, payout SLA, kontrak, dan aturan komersial Xendit harus dikonfirmasi secara tertulis.

## 6. Istilah dan status finansial

| Istilah | Makna |
|---|---|
| Booking | Permintaan layanan; belum memerlukan pembayaran pada saat dibuat. |
| Service lifecycle | Status operasional dari booking, konfirmasi, penitipan, permintaan pengambilan, serah-terima, hingga selesai. |
| Close-out invoice | Tagihan final yang dibuat ketika customer meminta pengambilan dan layanan akan diselesaikan. |
| Customer payment | Pembayaran customer atas tagihan final menggunakan Xendit. |
| Platform commission | 20% dari biaya penitipan, tidak termasuk biaya gateway. |
| Vendor share | 80% dari biaya penitipan, sebelum perlakuan biaya/penyesuaian lain yang telah disetujui. |
| Customer gateway fee | Biaya gateway yang ditampilkan pada invoice dan ditagihkan kepada customer jika mekanismenya diperbolehkan. |
| Split | Pembagian dana transaksi di antara akun penerima di dalam produk marketplace provider; belum tentu berarti uang telah tiba di bank vendor. |
| Vendor payout | Instruksi pengiriman dana vendor ke rekening bank vendor. |
| Settlement bank | Proses provider/bank sampai dana benar-benar tersedia pada rekening tujuan. |
| Webhook | Notifikasi server-ke-server dari provider; harus diverifikasi, disimpan, dan diproses secara idempotent. |
| Rekonsiliasi | Pencocokan ledger internal dengan data provider dan payout; owner: Super Admin. |

## 7. Opsi teknis

### Opsi A — Xendit xenPlatform

**Kandidat utama untuk diuji.** Dokumentasi Xendit menjelaskan xenPlatform sebagai solusi marketplace/platform untuk membuat dan mengelola sub-account, menerima pembayaran, membagi/routing pembayaran, memantau transaksi, serta payout ke rekening bank merchant. Dokumentasinya juga menjelaskan split rule yang mengarahkan dana setelah transaksi pembayaran berhasil dan dana settlement tersedia.

Kecocokan untuk TitipSini:

- Mendukung model platform dengan vendor sebagai merchant/sub-account, jika onboarding dan eligibility TitipSini disetujui.
- Memungkinkan pencatatan serta pembagian komisi melalui split rule atau transfer yang didukung.
- Menawarkan titik integrasi yang perlu dibuktikan untuk skenario: booking gratis → customer meminta pengambilan → invoice dibuat → customer membayar → serah-terima terkonfirmasi → vendor payout dipicu.

Hal yang wajib dikonfirmasi:

- Apakah vendor individu dapat di-onboard sesuai model, dokumen, status bisnis, dan pemeriksaan yang diwajibkan Xendit.
- Apakah pembayaran dapat dibuat pada saat close-out dan dirutekan sesuai bagian vendor serta komisi platform.
- Apakah 20% komisi TitipSini dapat dialokasikan otomatis, dan apakah split rule dapat digunakan pada metode pembayaran yang dipilih.
- Apakah payout ke bank dapat dipicu secara programatis setelah serah-terima terkonfirmasi dan apa SLA aktualnya.
- Apakah arsitektur yang disarankan memerlukan balance sub-account sebelum payout bank, atau tersedia model lain yang disetujui.
- Bagaimana biaya gateway customer dikenakan tanpa membuat total, split, dan nominal settlement tidak konsisten.
- Bagaimana menangani split gagal sesudah payment sukses, payout gagal, refund/koreksi, chargeback/dispute, dan rekonsiliasi.

Dokumentasi resmi untuk validasi:
- [XenPlatform overview](https://docs.xendit.co/docs/xenplatform-overview)
- [Route & split payments for sub-accounts](https://docs.xendit.co/docs/split-payments)
- [Create split rule API](https://docs.xendit.co/apidocs/create-split-rule)
- [Create a session / hosted checkout](https://docs.xendit.co/apidocs/create-session)
- [Transfer sub-account balances](https://docs.xendit.co/docs/transfer-balances)
- [Create sub-accounts](https://docs.xendit.co/docs/xenplatform-global-accounts-setup)

### Opsi B — Provider marketplace lain

Dapat menjadi pembanding jika Xendit tidak mendukung payout sesuai flow, biaya, persyaratan vendor individu, atau model bisnis TitipSini. Evaluasi provider alternatif hanya jika proof of concept atau jawaban tertulis Xendit menunjukkan ada blocker yang berarti.

### Opsi C — Payment gateway standar dengan payout terpisah

Customer membayar melalui gateway ke akun penerima yang ditetapkan, lalu backend mengirim payout ke vendor setelah service close-out. Opsi ini mungkin memerlukan kontrol ledger dan rekonsiliasi yang lebih kompleks, sehingga bukan pilihan utama jika xenPlatform dapat memenuhi kebutuhan.

## 8. Rekomendasi dan status keputusan

**Rekomendasi:** prioritaskan Xendit xenPlatform untuk proof of concept, tetapi jangan menganggap split rule otomatis menjamin payout bank segera setelah layanan selesai. Validasi alur end-to-end melalui sandbox dan konfirmasi tertulis provider sebelum mengunci arsitektur produksi.

Keputusan tetap berstatus `PROPOSED` sampai seluruh kondisi berikut terpenuhi:

1. Xendit mengonfirmasi eligibility TitipSini dan onboarding vendor individu.
2. Flow pembayaran saat close-out dapat dilaksanakan untuk metode pembayaran terpilih.
3. Split komisi 20% dan penyaluran bagian vendor ke bank dapat direkonsiliasi per booking.
4. Payout dapat dipicu setelah status layanan selesai dengan event/provider status yang dapat dipercaya.
5. Fee customer, fee provider, biaya transfer, batas transaksi, payout SLA, dan metode settlement disetujui oleh Product Owner/Finance.
6. Skenario gagal, webhook duplikat, payout tertunda, dan rekonsiliasi berhasil diuji.

## 9. Alur bisnis target

### 9.1 Booking sampai layanan dimulai

1. Customer memilih listing, lokasi, jenis barang, periode/durasi perkiraan, dan informasi yang diperlukan.
2. Backend memvalidasi ketersediaan dan harga berdasarkan konfigurasi listing.
3. Booking dibuat dengan status `pending_vendor_confirmation`.
4. Tidak ada payment intent, checkout, atau kewajiban pembayaran pada saat booking.
5. Vendor mengonfirmasi atau menolak booking.
6. Setelah barang benar-benar diterima, vendor melakukan check-in dan status layanan berubah menjadi `in_progress`.
7. Sistem mencatat waktu mulai serta data kondisi barang yang diperlukan untuk bukti serah-terima.

### 9.2 Customer meminta pengambilan / close-out

1. Customer menekan aksi seperti **“Akhiri Penitipan & Ambil Barang”** dari halaman booking yang sedang berjalan.
2. Sistem merekam `pickup_requested_at` dan menghentikan akumulasi biaya pada waktu yang ditetapkan menurut aturan tarif. Jika vendor perlu memverifikasi waktu atau kondisi barang, status dapat menjadi `awaiting_final_quote`.
3. Backend menentukan biaya akhir dari aturan harga yang tersimpan, durasi aktual, biaya tambahan yang telah disetujui, diskon, serta pembulatan.
4. Sistem membuat close-out invoice immutable yang memuat: biaya penitipan, komisi TitipSini (untuk ledger; bukan charge tambahan di atas tarif kecuali kebijakan harga menyatakannya), biaya gateway customer, total yang harus dibayar, dan nilai bersih vendor.
5. Customer meninjau rincian final dan melanjutkan checkout Xendit.
6. Jika checkout gagal atau kedaluwarsa, barang belum boleh diserahkan. Customer dapat mencoba lagi melalui invoice yang sama atau invoice baru menurut status provider, tanpa menghasilkan pembayaran ganda.
7. Webhook/status inquiry server-to-server memastikan pembayaran sukses. Redirect dari browser hanya untuk UX, bukan sumber kebenaran.
8. Setelah payment terkonfirmasi, vendor menyerahkan barang. Serah-terima dicatat melalui konfirmasi customer/vendor atau mekanisme bukti yang dipilih, misalnya OTP.
9. Setelah serah-terima dikonfirmasi, layanan menjadi `completed`.
10. Backend membuat satu instruksi payout untuk bagian vendor, atau memastikan split/provider transfer berjalan sesuai arsitektur yang disetujui. Instruksi dibuat otomatis segera setelah semua syarat terpenuhi.
11. Sistem hanya menandai `vendor_payout_succeeded` jika status provider membuktikannya. Status “instruksi payout dibuat” tidak sama dengan “uang masuk bank”.
12. Super Admin menerima alert jika payment sukses tetapi split/payout gagal atau belum selesai dalam SLA.

### 9.3 Aturan penting terkait urutan

Untuk mencegah barang lepas tanpa pembayaran, rekomendasi default adalah **customer membayar ketika meminta pengakhiran/pengambilan, sebelum barang diserahkan secara fisik**. Booking baru berubah menjadi `completed` setelah payment sukses dan serah-terima dikonfirmasi. Payout vendor baru dipicu setelah service completion tercatat.

Urutan teknis provider harus dibuktikan: jika Xendit hanya dapat melakukan split saat payment settled, aplikasi tetap harus menjaga state internal agar booking, payment, dan payout tidak keliru diselesaikan saat provider masih memproses dana.

## 10. Formula biaya dan komisi

Semua uang disimpan sebagai integer rupiah (`BIGINT`/integer minor unit sesuai API), bukan floating-point. Aturan pembulatan harus konsisten dan diuji.

```text
storage_fee            = biaya penitipan final sesuai tarif dan durasi aktual
platform_commission    = round(storage_fee * 20%)
vendor_share            = storage_fee - platform_commission
customer_gateway_fee   = fee gateway yang ditampilkan dan ditagih secara sah kepada customer
customer_total          = storage_fee + customer_gateway_fee
```

Ketentuan:

- Komisi 20% dihitung dari `storage_fee`, bukan dari `customer_gateway_fee`.
- `vendor_share` adalah 80% dari biaya penitipan sebelum penyesuaian lain yang secara eksplisit disepakati.
- Pembayaran customer meliputi biaya penitipan serta biaya gateway yang ditampilkan sebagai komponen terpisah, jika diizinkan provider. Pastikan penetapan biaya tidak melanggar ketentuan provider atau aturan yang berlaku.
- Fee provider aktual dapat berbeda tergantung metode, transaksi, dan skema marketplace. Simpan fee aktual dari laporan provider untuk rekonsiliasi; jangan mengasumsikan biaya yang dibayar customer otomatis sama persis dengan potongan provider tanpa bukti.
- Jika harga berubah saat close-out karena perpanjangan atau durasi aktual, customer harus melihat total final sebelum membayar.
- Simpan snapshot aturan komisi dan tarif pada setiap invoice, agar perubahan konfigurasi di masa depan tidak mengubah transaksi lama.

Contoh non-produksi: jika `storage_fee = Rp100.000`, komisi TitipSini adalah Rp20.000 dan vendor share Rp80.000. Jika ada gateway fee customer, nominalnya ditambahkan sebagai baris terpisah sesuai perhitungan dan ketentuan provider. Nilai bersih final harus divalidasi dengan laporan settlement aktual Xendit.

## 11. Prinsip desain teknis Laravel

### 11.1 Modul domain

- `Bookings`: pembuatan booking, konfirmasi vendor, pembatalan sebelum layanan, ketersediaan.
- `StorageServices`: check-in, status `in_progress`, permintaan pengambilan, perhitungan durasi, serah-terima, `completed`.
- `CloseoutInvoices`: final quote, snapshot biaya, invoice immutable, expiry, pembukaan ulang jika checkout gagal.
- `Payments`: pembuatan checkout Xendit saat close-out, payment reference, status pembayaran customer.
- `MarketplaceRouting`: aturan split 20%/80% jika didukung untuk skenario yang disetujui.
- `VendorPayouts`: instruksi payout setelah service completion, retry aman, status provider, kegagalan.
- `FinanceLedger`: pencatatan biaya penitipan, komisi, fee gateway, bagian vendor, transfer, payout, koreksi dan refund bila diperlukan.
- `PaymentWebhooks`: verifikasi tanda tangan/secret sesuai dokumen Xendit, deduplikasi, penyimpanan event dan pemrosesan idempotent.
- `VendorAccounts`: profil individu, rekening bank, akun/sub-account provider, status verifikasi.
- `VendorDocuments`: metadata dan workflow verifikasi SKCK, dengan penyimpanan privat dan akses terbatas.
- `Reconciliation`: laporan provider, selisih dan daftar tugas yang dimiliki Super Admin.
- `AuditLog`: jejak untuk perubahan sensitif oleh customer, vendor, admin, dan sistem.

### 11.2 Interface provider

Pisahkan pembayaran customer dari payout vendor. Nama metode di bawah bersifat ilustratif; sesuaikan dengan API Xendit yang dipilih dalam proof of concept.

```php
interface PaymentGatewayInterface
{
    public function createCloseoutCheckout(CloseoutPaymentData $data): CheckoutSessionData;

    public function getPaymentStatus(string $providerPaymentId): ProviderPaymentStatus;

    public function verifyWebhook(array $headers, string $rawBody): VerifiedProviderEvent;
}

interface MarketplaceSettlementInterface
{
    public function routeCommissionAndVendorShare(SettlementRequestData $data): SettlementInstructionData;

    public function createVendorPayout(VendorPayoutRequestData $data): VendorPayoutResultData;

    public function getVendorPayoutStatus(string $providerReference): VendorPayoutStatusData;
}
```

Interface dan implementasi final tidak boleh menjanjikan bahwa provider mendukung split atau payout pada tahap tertentu sebelum dikonfirmasi. Jika provider otomatis melakukan split saat settlement, orchestration layer harus mengikuti event dan status provider tanpa menandai payout bank sukses terlalu awal.

### 11.3 Model data awal

| Entitas | Tujuan / field penting |
|---|---|
| `bookings` | Customer, vendor, listing, periode estimasi, status booking. Tidak memerlukan payment saat dibuat. |
| `storage_services` | Booking, `service_started_at`, `pickup_requested_at`, `handover_confirmed_at`, `completed_at`, status dan bukti serah-terima. |
| `closeout_invoices` | Booking/service, versi invoice, biaya penitipan, komisi, gateway fee, total customer, vendor share, status invoice, timestamps. |
| `payments` | Invoice, provider, provider payment ID, reference, amount, status, channel, succeeded/expired timestamps. Dibuat saat close-out, bukan saat booking. |
| `payment_events` | ID event provider unik, tipe, payload tersanitasi, waktu diterima, hasil pemrosesan. |
| `vendor_payment_accounts` | Vendor, Xendit account/sub-account ID, status onboarding/KYC, rekening/destination reference, payment/payout eligibility. |
| `booking_financial_snapshots` | Snapshot harga, komisi 20%, fee gateway, nilai vendor, formula/versi aturan. |
| `vendor_payouts` | Invoice, vendor, nominal, trigger service completion, idempotency key, provider payout ID, status dan timestamps. |
| `provider_transfers` | Transfer/split provider jika arsitektur Xendit yang disetujui memerlukannya. |
| `financial_ledger_entries` | Entri debit/kredit internal yang immutable untuk payment, komisi, vendor share, fee, payout, dan koreksi. |
| `refunds_or_adjustments` | Refund/koreksi untuk exception, bukan alur normal pembatalan pra-layanan. |
| `financial_reconciliations` | Periode/laporan, provider reference, nilai cocok/selisih, status penyelesaian, owner Super Admin. |
| `audit_logs` | Aktor, tindakan, timestamp, reference, metadata aman tanpa secret atau data sensitif berlebihan. |

### 11.4 Status yang disarankan

**Booking/service status** (detail implementasi bisa digabungkan atau dipisah, tetapi domain-nya harus tetap jelas):

- `pending_vendor_confirmation`
- `confirmed`
- `in_progress`
- `pickup_requested`
- `awaiting_final_quote`
- `awaiting_payment`
- `payment_succeeded_awaiting_handover`
- `handover_confirmed`
- `completed`
- `cancelled`
- `disputed`

**Payment status:**

- `not_created` — default selama booking dan layanan berjalan.
- `pending`
- `succeeded`
- `failed`
- `expired`
- `refund_pending` / `refunded` — hanya untuk koreksi/refund yang memang disetujui dan dieksekusi.

**Vendor payout status:**

- `not_eligible`
- `eligible`
- `queued`
- `processing`
- `succeeded`
- `failed`
- `needs_reconciliation`

Jangan menyamakan `service completed` dengan `payout succeeded`. Service dapat telah selesai sementara payout masih diproses provider/bank; dashboard perlu menunjukkan status itu dengan jelas.

## 12. Invariants dan aturan bisnis wajib

1. Booking dan layanan berjalan tanpa membuat payment intent atau mengharuskan pembayaran.
2. Close-out invoice hanya dibuat ketika customer meminta pengambilan/penyelesaian layanan dan data biaya telah divalidasi.
3. Satu invoice final memiliki reference unik dan snapshot harga yang tidak berubah setelah pembayaran dimulai. Perubahan biaya memerlukan versi invoice baru yang dapat diaudit.
4. Customer tidak boleh diminta membayar lebih dari satu kali untuk invoice yang sama; retry checkout harus menggunakan kontrol idempotensi.
5. Browser redirect bukan bukti final pembayaran. Payment `succeeded` harus berdasarkan event terverifikasi dan/atau status inquiry server-to-server.
6. Barang tidak boleh ditandai telah diserahkan sebelum payment sukses, kecuali pengecualian disetujui dengan role, alasan, dan audit log.
7. Payout tidak boleh dipicu sebelum payment sukses dan layanan/serah-terima terkonfirmasi selesai.
8. Payout per invoice tidak boleh ganda. Gunakan idempotency key unik dan unique constraint di database.
9. Komisi = 20% dari biaya penitipan pada snapshot invoice; gateway fee bukan basis komisi.
10. Vendor payout hanya boleh menuju rekening/vendor provider account yang telah lolos verifikasi dan aktif.
11. Gagal split/payout tidak boleh mengubah payment sukses menjadi gagal. Catat sebagai status keuangan terpisah dan buat tugas rekonsiliasi untuk Super Admin.
12. Status provider harus diproses idempotent walau webhook duplikat atau out-of-order.
13. Semua operasi uang menggunakan decimal/integer currency yang aman; hindari float.
14. Pembatalan sebelum barang dititipkan normalnya tidak memerlukan refund karena payment belum dimulai. Jika payment exception memang sudah tercipta, gunakan alur koreksi/refund yang diaudit.
15. Setelah layanan dimulai, pembatalan bukan berarti layanan tanpa biaya; biaya yang telah timbul pada saat close-out dihitung sesuai kebijakan harga dan dispute policy.
16. Tidak ada notifikasi atau workflow WhatsApp dalam scope.

## 13. Keamanan dan operasional

- Simpan API secret dan webhook secret di secret manager/environment, bukan di repository.
- Verifikasi signature/token webhook sesuai dokumentasi provider; validasi timestamp/duplicate event jika didukung.
- Catat raw event seperlunya dengan akses terbatas; jangan menyimpan secret, data kartu, atau payload sensitif tanpa kebutuhan.
- Gunakan transaksi database, locking, unique reference dan idempotency pada perubahan status payment/payout.
- Jangan mengandalkan halaman sukses checkout untuk mengubah status payment.
- Gunakan hosted checkout/tokenisasi provider agar aplikasi tidak memproses atau menyimpan data kartu sensitif.
- Batasi perubahan nominal invoice, komisi, payout destination dan status layanan kepada role yang sesuai.
- Semua perubahan manual oleh Super Admin untuk exception wajib mencatat alasan, actor, waktu, nilai sebelum/sesudah, dan reference transaksi.
- Lindungi data rekening bank dan SKCK; gunakan storage private, authorization, enkripsi bila sesuai, retensi dan penghapusan yang terdefinisi.
- Monitoring minimum: webhook gagal/terlambat, invoice menunggu pembayaran, payment sukses tanpa handover, service selesai tanpa payout, payout gagal/tertunda, split gagal, duplicate event, serta selisih rekonsiliasi.
- Sediakan prosedur operasional untuk payout gagal, payment sukses tetapi barang belum diserahkan, customer menyangkal pembayaran, vendor tidak tersedia setelah pembayaran, dan sengketa harga close-out.

## 14. Pembatalan, koreksi, dan edge case

### Pembatalan sebelum barang dititipkan

Booking dapat dibatalkan sesuai kebijakan bisnis. Karena pembayaran normalnya belum dibuat, sistem cukup membatalkan booking dan melepaskan kapasitas; tidak ada refund yang diperlukan kecuali terdapat payment yang tidak semestinya berhasil.

### Customer tidak jadi mengambil barang setelah meminta close-out

- Jika invoice belum dibayar: invoice dapat kedaluwarsa atau dibatalkan sesuai aturan, dan layanan tetap ditandai sesuai keadaan fisik barang; jangan menandai service completed sebelum handover.
- Jika payment sudah sukses tetapi serah-terima belum berlangsung: jangan membuat payout. Buat kasus operasional untuk menyelesaikan handover atau koreksi transaksi. Aturan refund/koreksi untuk kasus ini harus ditentukan sebelum produksi.

### Payment sukses tetapi webhook terlambat

Lakukan status inquiry server-to-server dan proses event secara idempotent. Jangan meminta customer membayar lagi sebelum status transaksi lama diketahui.

### Payment sukses tetapi split/payout gagal

Pertahankan payment sebagai sukses, tandai settlement/payout gagal atau perlu rekonsiliasi, simpan nilai yang tertahan pada akun terkait, dan tampilkan tugas follow-up kepada Super Admin. Retry hanya setelah status sebelumnya diketahui supaya tidak ada transfer ganda.

### Vendor payout gagal

Jangan mengubah service status menjadi belum selesai. Tandai payout failed, alert Super Admin, lakukan koreksi rekening/onboarding jika perlu, lalu retry menggunakan idempotency dan prosedur yang dapat diaudit.

### Sengketa jumlah biaya

Invoice final harus menyimpan rincian durasi, tarif, biaya tambahan, dan bukti perubahan yang disetujui. Tetapkan apakah diperlukan review vendor/admin sebelum checkout; jangan mengubah invoice setelah payment berhasil tanpa adjustment record.

## 15. Proof of concept dan pengujian

POC dilakukan menggunakan sandbox dan vendor/customer uji. Tidak ada dana produksi yang digunakan.

1. Buat booking tanpa payment, vendor konfirmasi, lalu tandai layanan mulai.
2. Pastikan payment tidak dibuat ketika booking diciptakan atau ketika layanan berjalan.
3. Customer meminta pengambilan; sistem membuat invoice sesuai durasi dan tarif final.
4. Uji checkout berhasil, gagal, kedaluwarsa, pembatalan checkout, retry, dan perbedaan antara redirect browser dan webhook.
5. Pastikan komisi 20%, vendor share, gateway fee customer, dan customer total dihitung benar.
6. Pastikan payment sukses diikuti proses serah-terima, service completion, serta trigger payout otomatis.
7. Uji split/payout status pending, success, failed, duplicate request, saldo tidak cukup, rekening tidak valid, dan status yang belum pasti.
8. Pastikan sistem tidak melakukan payout sebelum service complete dan tidak membuat payout ganda.
9. Rekonsiliasi invoice, payment, fee provider, komisi, split/transfer, payout dan laporan Xendit.
10. Uji pembatalan sebelum layanan tanpa payment serta kasus exception payment yang tidak semestinya berhasil.
11. Pastikan Super Admin dapat melihat kasus bermasalah, bukti, status provider, nilai dan tindakan follow-up.

## 16. Acceptance criteria sebelum produksi

- [ ] Booking dapat dibuat dan dikonfirmasi tanpa checkout/payment.
- [ ] Layanan dapat berjalan tanpa pembayaran di awal.
- [ ] Aksi pengambilan barang memicu penetapan biaya akhir dan invoice.
- [ ] Customer melihat komponen biaya dan total sebelum checkout.
- [ ] Payment hanya dianggap sukses setelah status terverifikasi dari provider.
- [ ] Serah-terima normal tidak dapat diselesaikan sebelum payment sukses.
- [ ] Setelah payment sukses dan handover terkonfirmasi, service ditandai completed.
- [ ] Sistem memicu satu payout instruksi setelah service complete, menggunakan mekanisme Xendit yang sudah dikonfirmasi.
- [ ] Status payout tidak dianggap sukses tanpa bukti provider.
- [ ] Komisi 20% dihitung dari biaya penitipan, bukan gateway fee.
- [ ] Fee gateway customer ditampilkan secara terpisah dan disetujui provider.
- [ ] Webhook duplikat/out-of-order tidak menghasilkan perubahan atau payout ganda.
- [ ] Payout gagal dan perbedaan rekonsiliasi muncul sebagai tugas Super Admin.
- [ ] Pembatalan sebelum barang dititipkan tidak memerlukan pembayaran pada alur normal.
- [ ] Tidak ada integrasi WhatsApp.
- [ ] Vendor individu, rekening bank, dan metode onboarding telah disetujui Xendit.
- [ ] Ketentuan data SKCK, akses, retensi, dan penghapusan siap sebelum pendaftaran vendor dibuka.
- [ ] Dokumen alur kegagalan dan dukungan operasional telah diuji oleh Finance/Operations.

## 17. Pertanyaan untuk Xendit sebelum keputusan final

1. Apakah TitipSini dapat diaktifkan sebagai platform xenPlatform untuk marketplace penitipan dengan vendor perorangan?
2. Apakah setiap vendor perlu dibuat sebagai sub-account/merchant, dan apa dokumen verifikasi serta proses onboarding yang diwajibkan?
3. Untuk invoice yang baru dibuat saat customer akan mengambil barang, apa pola integrasi yang direkomendasikan: pembayaran atas nama platform dengan split rule, pembayaran atas nama sub-account dengan komisi platform, atau pola lain?
4. Bagaimana bagian 80% vendor diarahkan sampai ke rekening bank setelah customer membayar dan serah-terima dinyatakan selesai? Apakah membutuhkan split ke saldo sub-account diikuti payout bank?
5. Apakah API payout dapat dipanggil otomatis setelah event `service completed` dari TitipSini? Apa status, batas, biaya, cut-off, dan SLA bank payout?
6. Bagaimana cara mengenakan biaya gateway kepada customer secara transparan dan diizinkan, serta bagaimana biaya aktual tercermin pada laporan provider?
7. Apakah split fee 20% dapat diproses dengan metode pembayaran yang dipilih dan bagaimana perilaku jika split gagal setelah payment sukses?
8. Apa proses yang direkomendasikan untuk payment sukses tetapi barang tidak jadi diserahkan, dispute biaya, chargeback, atau refund/koreksi?
9. Apa format laporan dan webhook yang tersedia untuk rekonsiliasi payment, fee, split/transfer, dan payout?
10. Apakah terdapat limit transaksi/settlement, waktu proses di akhir pekan/hari libur, dan persyaratan tambahan berdasarkan volume?

## 18. Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Xendit tidak mendukung trigger payout yang diperlukan | POC sebelum produksi; jangan mengunci desain ke asumsi provider; evaluasi arsitektur alternatif jika perlu. |
| Payment sukses tetapi barang belum diserahkan | Service state terpisah, payout gate, task operasional dan prosedur koreksi. |
| Split/payout gagal sementara payment sukses | Ledger internal, monitoring, idempotent retry, rekonsiliasi Super Admin. |
| Fee gateway yang ditagih ke customer tidak cocok dengan fee aktual | Validasi kontrak dan formula; tampilkan fee dengan benar; rekonsiliasi nilai aktual provider. |
| Biaya akhir diperdebatkan | Formula transparan, snapshot tarif, bukti check-in/out dan invoice final yang immutable. |
| Payout ganda akibat webhook/retry | Unique reference, idempotency key, row locking, serta pengecekan status provider sebelum retry. |
| Onboarding vendor individu tidak disetujui | Konfirmasi eligibility dan KYC Xendit lebih awal sebelum membangun workflow final. |
| Kebocoran rekening bank atau SKCK | Least-privilege access, storage privat, audit, retensi dan penghapusan. |

## 19. Ringkasan keputusan yang telah diketahui

| Topik | Keputusan/arah |
|---|---|
| Provider prioritas | Xendit; xenPlatform kandidat pertama untuk POC. |
| Pembayaran saat booking | Tidak ada. |
| Waktu membuat tagihan | Saat customer ingin mengakhiri layanan dan mengambil barang. |
| Waktu customer membayar | Melalui checkout pada tahap close-out, sebelum serah-terima normal dilakukan. |
| Waktu payout vendor | Setelah payment sukses dan serah-terima/service completion terkonfirmasi; instruksi otomatis segera dibuat. |
| Komisi TitipSini | 20% dari biaya penitipan. |
| Bagian vendor | Secara prinsip 80% dari biaya penitipan, sebelum penyesuaian lain yang telah disepakati. |
| Biaya gateway | Ditanggung customer, jika mekanisme penagihan disetujui provider dan aturan yang berlaku. |
| Vendor | Individu dengan syarat SKCK dan onboarding/payment account yang disetujui provider. |
| Pembatalan pra-titip | Diizinkan; normalnya tidak perlu refund karena belum ada pembayaran. |
| WhatsApp | Tidak digunakan. |
| Pemilik rekonsiliasi | Super Admin. |
| Status ADR | `PROPOSED` sampai POC, biaya, eligibility, dan flow settlement disepakati. |

## 20. Referensi dan catatan

Dokumentasi provider dapat berubah; periksa ulang saat implementasi. Link berikut adalah titik awal untuk proof of concept, bukan bukti bahwa seluruh flow TitipSini telah disetujui Xendit.

- [Xendit — XenPlatform overview](https://docs.xendit.co/docs/xenplatform-overview)
- [Xendit — Route & split payments for sub-accounts](https://docs.xendit.co/docs/split-payments)
- [Xendit API — Create split rule](https://docs.xendit.co/apidocs/create-split-rule)
- [Xendit API — Create a session](https://docs.xendit.co/apidocs/create-session)
- [Xendit — Transfer sub-account balances](https://docs.xendit.co/docs/transfer-balances)
- [Xendit — Create sub-accounts](https://docs.xendit.co/docs/xenplatform-global-accounts-setup)

Catatan teknis dari dokumentasi publik Xendit: xenPlatform mendukung konsep master account/sub-account dan split/routing; dokumentasi split menjelaskan bahwa split dapat diproses setelah payment sukses dan dana settle. Jika split gagal, dana dapat tetap berada di saldo akun sumber penerima sehingga aplikasi membutuhkan rekonsiliasi dan mekanisme pemulihan. Karena itu, TitipSini harus membuktikan urutan payment → handover → service completion → vendor payout dalam sandbox dan mengonfirmasi model yang benar bersama Xendit.

## 21. Riwayat perubahan

| Versi | Tanggal | Status | Perubahan |
|---|---|---|---|
| 1.0 | 2026-10-27 | PROPOSED | Proposal awal pemilihan provider dan payout marketplace. |
| 1.1 | 2026-10-27 | PROPOSED | Menambahkan preferensi bisnis: Xendit, komisi 20%, fee gateway customer, vendor individu dengan SKCK, payout ke rekening vendor, tanpa WhatsApp, rekonsiliasi Super Admin. |
| 1.2 | 2026-10-27 | PROPOSED | Mengoreksi flow utama: booking dan layanan berjalan tanpa pembayaran; invoice dibuat saat customer ingin mengakhiri layanan dan mengambil barang; payout vendor dipicu setelah payment sukses serta serah-terima/service completion terkonfirmasi. Menghapus asumsi dana customer ditahan selama masa penitipan dan memperjelas status booking, payment, layanan, split, dan payout sebagai state terpisah. |
