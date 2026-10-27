---
title: "Implementation Plan — TitipSini Website Terintegrasi"
product: "TitipSini"
document_type: "Implementation Plan"
version: "2.1"
date: "2026-10-27"
status: "Draft — build from zero dengan Laravel/PHP dan target settlement pembayaran langsung ke vendor melalui payment gateway"
language: "id-ID"
source_documents:
  - "PRD_TitipSini_WebTerintegrasi.md"
project_decisions:
  starting_point: "Build from zero"
  backend_stack: "Laravel / PHP"
  payment_model: "Automated payment gateway; target settlement langsung ke akun vendor melalui fitur marketplace/split settlement yang didukung provider; provider TBD"
  fund_flow: >-
    Customer membayar melalui payment gateway; target settlement ke akun vendor melalui fitur marketplace/split settlement provider; jangan mengalihkan dana ke akun TitipSini sebagai fallback tanpa persetujuan eksplisit. Provider dan dukungan fiturnya masih harus divalidasi.
  whatsapp_role: "Undecided; integration must remain optional"
primary_audience:
  - Product Owner
  - Engineering
  - UI/UX
  - QA
  - Operations
  - AI Coding Agent
---

# Implementation Plan — TitipSini Website Terintegrasi

## 1. Tujuan Dokumen

Dokumen ini menerjemahkan `PRD_TitipSini_WebTerintegrasi.md` menjadi rencana implementasi untuk proyek **baru dari nol**, menggunakan **Laravel/PHP**, dengan **payment gateway otomatis** sebagai bagian alur transaksi dan target **settlement langsung ke akun vendor melalui fitur marketplace/split settlement provider**. Peran WhatsApp belum diputuskan, sehingga arsitektur harus memungkinkan WhatsApp ditambahkan kemudian tanpa menjadikannya dependensi inti booking atau pembayaran.

Tujuan produk adalah memindahkan pencatatan dan pengelolaan transaksi dari percakapan/manual workflow ke website terintegrasi. Website dan database menjadi **single source of truth** untuk katalog, booking, pembayaran, status penitipan, bukti serah-terima, bantuan pelanggan, dan laporan.

> Keputusan yang telah diberikan: proyek dibangun dari nol; stack backend adalah Laravel/PHP; pembayaran otomatis diproses melalui payment gateway; targetnya dana disalurkan ke vendor melalui gateway, bukan dikumpulkan terlebih dahulu di akun TitipSini; peran WhatsApp belum ditentukan. Provider gateway dan validasi bahwa provider mendukung akun vendor/sub-merchant, settlement langsung/split settlement, komisi platform, refund, webhook settlement, dan rekonsiliasi masih terbuka. Jangan menganggap semua gateway mendukung fitur marketplace.

## 2. Sasaran Produk dan Prinsip Implementasi

### 2.1 Hasil yang ingin dicapai

1. Customer dapat menemukan layanan, memahami harga/aturan, membuat booking, membayar secara online, dan melacak status dari website.
2. Vendor dapat mengelola listing, ketersediaan, permintaan booking, check-in/check-out, dan bukti serah-terima melalui portal vendor.
3. Admin dapat mengelola verifikasi, listing, booking berbantuan, bantuan pelanggan, dan monitoring operasional dari dashboard.
4. Finance dapat menelusuri payment reference, rekonsiliasi, refund, komisi, dan payout sesuai model bisnis yang disetujui.
5. Semua sumber booking, termasuk lead dari WhatsApp jika masih digunakan, dicatat di sistem yang sama.
6. Pembayaran dinyatakan berhasil berdasarkan konfirmasi backend yang terverifikasi dari provider, bukan berdasarkan redirect browser customer.

### 2.2 Prinsip implementasi

- **Build clean, not overengineer:** mulai dengan modular monolith Laravel; jangan memulai dari microservices.
- **Server is authoritative:** harga, kapasitas, izin akses, status booking, dan status pembayaran selalu divalidasi di server.
- **One canonical record:** seluruh booking dan pembayaran mempunyai record resmi dalam database.
- **Idempotency by design:** request berulang, klik ganda, job retry, dan webhook duplikat tidak boleh menggandakan booking atau operasi finansial.
- **Separate lifecycles:** status booking, payment, refund, handover, support ticket, dan notification tidak digabung dalam satu kolom.
- **Security by default:** authorization server-side, audit log, perlindungan dokumen, secret management, backup, dan minimisasi data.
- **Provider adapters:** payment gateway dan kanal notifikasi dibungkus interface agar provider dapat diganti.
- **Milestone gates:** lanjut ke milestone berikutnya setelah acceptance criteria dan test kritis terpenuhi.
- **Do not block core product on WhatsApp:** katalog, booking, pembayaran, dan halaman status harus berfungsi walaupun integrasi WhatsApp belum diputuskan.

## 3. Ruang Lingkup MVP

### 3.1 P0 — wajib untuk MVP

- Website publik responsif: landing page, katalog, search/filter, detail listing/vendor, FAQ, kebijakan, dan kontak bantuan.
- Customer account: registrasi, login, reset akses, profil, dan daftar booking.
- Vendor account dan portal operasional.
- Dashboard admin untuk verifikasi, moderasi, booking berbantuan, dan dukungan operasional.
- Manajemen kategori, lokasi, listing, harga, aturan, kapasitas, dan ketersediaan.
- Booking ID, kalkulasi harga server-side, validasi ketersediaan, dan timeline status.
- Payment gateway otomatis dengan sandbox test, payment reference, verified webhook, idempotency, dan rekonsiliasi dasar untuk pembayaran serta settlement vendor langsung. Provider harus mendukung model marketplace/split settlement yang dibutuhkan.
- Pemberitahuan status melalui kanal dasar yang disepakati. WhatsApp dapat ditambahkan setelah keputusan provider dan use case ditetapkan.
- Check-in/check-out dan bukti serah-terima.
- Tiket bantuan yang terkait dengan booking.
- Role-based access control (RBAC), audit log, error logging, backup, monitoring, dan CI.
- Dokumentasi deployment, operasi, payment reconciliation, serta penanganan insiden.

### 3.2 Tidak masuk MVP kecuali disetujui terpisah

- Aplikasi native iOS/Android.
- Chat real-time penuh di website.
- Impor seluruh histori percakapan WhatsApp.
- AI chatbot yang dapat mengubah booking atau status keuangan secara mandiri.
- Dynamic pricing, loyalty kompleks, live tracking, dan microservices.
- Multi-gateway routing/failover otomatis. Desain adapter boleh mendukung provider alternatif di masa depan, tetapi rilis awal menggunakan satu provider yang disepakati.
- Transfer/payout manual dari rekening TitipSini ke vendor bukan alur default. Target MVP adalah settlement vendor melalui fitur marketplace/split settlement provider; dukungan provider dan proses rekonsiliasinya wajib divalidasi sebelum integrasi final.

## 4. Keputusan Teknis Awal

### 4.1 Baseline yang direkomendasikan

| Area | Rekomendasi awal | Catatan |
|---|---|---|
| Backend | Laravel dengan PHP pada versi yang masih didukung saat proyek dimulai | Kunci versi setelah mengecek dukungan keamanan Laravel/PHP dan kompatibilitas hosting. |
| Arsitektur | Modular monolith | Satu aplikasi deployable dengan batas modul domain yang jelas. |
| Frontend | Default awal: Blade + Livewire + Tailwind CSS | Cocok untuk website SEO-friendly dan dashboard tanpa membangun SPA terpisah. Catat ADR jika tim memilih Inertia/Vue/React. Jangan mencampur beberapa pola tanpa kebutuhan. |
| Database | Satu database relasional: PostgreSQL atau MySQL | Pilih satu dalam ADR sebelum migration pertama; jangan mendukung dua engine secara paralel. |
| Queue/cache | Redis untuk production jika tersedia, dengan Laravel Queue | Job notifikasi, webhook follow-up, expiry, dan rekonsiliasi berjalan di background. |
| File storage | S3-compatible object storage atau layanan managed yang mendukung private objects | Dokumen verifikasi dan bukti privat tidak boleh tersimpan sebagai file publik. |
| Payment | Satu payment gateway yang mendukung onboarding vendor/sub-merchant dan settlement langsung/split settlement | Provider belum dipilih. Wajib validasi fitur, kontrak, refund, fee, webhook settlement, dan rekonsiliasi sebelum integrasi produksi. |
| Notifikasi | Notification service dengan channel adapter | Email/in-app dapat menjadi baseline; WhatsApp tetap opsi sampai keputusan dibuat. |
| Testing | PHPUnit atau Pest; pilih satu pendekatan yang konsisten | Wajib ada unit, integration, feature/API, dan end-to-end test untuk alur kritis. |
| CI/CD | Pipeline otomatis untuk quality checks, test, build, migration check, dan deployment | Tool dipilih sesuai hosting dan kebutuhan tim. |

Keputusan frontend dan database di atas adalah **rekomendasi awal**, bukan fakta yang telah diputuskan. Sebelum membuat banyak kode, catat keputusan melalui Architecture Decision Record (ADR).

### 4.2 Struktur aplikasi yang disarankan

Gunakan konvensi Laravel. Kelompokkan kode berdasarkan domain tanpa membangun framework internal yang terlalu rumit.

```text
app/
  Domain/
    Identity/
    Catalog/
    Availability/
    Booking/
    Payments/
    Operations/
    Support/
    Notifications/
  Http/
    Controllers/
    Middleware/
    Requests/
    Resources/
  Jobs/
  Models/
  Notifications/
  Policies/
  Providers/
  Rules/
  Services/
  Support/
bootstrap/
config/
database/
  factories/
  migrations/
  seeders/
resources/
  css/
  js/
  views/
routes/
  web.php
  api.php        # gunakan hanya jika benar-benar diperlukan
  console.php
  channels.php   # jika digunakan oleh versi Laravel yang dipilih
  webhooks.php   # boleh juga didefinisikan pada file route khusus
 tests/
  Unit/
  Feature/
  Browser/       # bila memakai browser E2E
```

Contoh batas domain:

- `Catalog`: kategori, vendor, lokasi, listing, media, aturan dan publikasi.
- `Availability`: kapasitas, slot, periode, dan penguncian ketersediaan.
- `Booking`: create/cancel, lifecycle, quote, snapshot harga, timeline.
- `Payments`: payment attempt, adapter provider, verified webhook, rekonsiliasi, refund.
- `Operations`: check-in/out, checklist dan bukti serah-terima.
- `Support`: tiket, assignment, komunikasi bantuan dan penyelesaian.
- `Notifications`: event-to-message mapping, queue, retry, preferensi dan provider adapter.

Jangan memaksa setiap domain menjadi package tersendiri. Gunakan service/action yang jelas dan dependency injection Laravel; hindari controller gemuk dan business logic di Blade.

## 5. Workstream 0 — Product Discovery dan Keputusan Fondasi

Walaupun proyek dimulai dari nol secara teknis, discovery bisnis tetap perlu dilakukan sebelum mengunci perilaku transaksi.

### Tasks

- [ ] Konfirmasi customer journey dan alur operasional target dari inquiry sampai pengembalian barang.
- [ ] Putuskan apakah booking menunggu persetujuan vendor atau langsung confirmed berdasarkan availability.
- [ ] Tetapkan cara menghitung harga, durasi, biaya tambahan, komisi dan pajak jika relevan.
- [ ] Tentukan kebijakan expiry booking pending payment, pembatalan, refund, dan sengketa.
- [x] Target aliran dana: customer membayar melalui payment gateway dan settlement disalurkan langsung ke akun vendor; dana tidak direncanakan masuk ke akun TitipSini untuk payout ulang.
- [ ] Pilih payment gateway yang secara kontraktual dan teknis mendukung marketplace/sub-merchant atau split settlement, termasuk onboarding/KYC vendor, komisi platform bila diperlukan, refund/reversal setelah settlement, webhook settlement, rekonsiliasi, sandbox, dan laporan fee.
- [ ] Jika tidak ada provider yang mendukung model target, hentikan keputusan integrasi final dan minta Product Owner menyetujui alternatif aliran dana secara eksplisit.
- [ ] Putuskan apakah WhatsApp dipakai untuk notifikasi satu arah, customer support, integrasi dua arah, atau tidak dipakai pada MVP.
- [ ] Tentukan kategori dan area pilot, jumlah vendor, serta perwakilan customer/vendor untuk UAT.
- [ ] Tentukan data awal yang akan dimasukkan (vendor/listing/customer/booking aktif) dan siapa yang berwenang memverifikasinya.
- [ ] Tentukan hosting, domain, email sender, object storage, queue dan backup.

### Output wajib

1. `docs/product/as-is-to-be.md` — proses yang direncanakan dan asumsi operasional.
2. `docs/decisions/ADR-001-architecture.md` — Laravel deployment/frontend/database baseline.
3. `docs/decisions/ADR-002-payment-gateway.md` — provider, settlement langsung ke vendor, komisi, refund/reversal, lifecycle pembayaran dan batas integrasi.
4. `docs/decisions/ADR-003-notifications-whatsapp.md` — keputusan kanal awal dan kapan WhatsApp diaktifkan.
5. `docs/open-questions.md` — pertanyaan terbuka, owner, dampak dan target keputusan.
6. MVP backlog berisi issue dengan acceptance criteria dan test plan.

**Exit criteria:** Product Owner menyetujui perilaku booking dan refund, menegaskan target settlement langsung ke vendor, serta menyepakati shortlist/provider owner untuk memverifikasi kemampuan marketplace/split settlement. Provider-specific integration tidak boleh difinalisasi sebelum dukungan ini terbukti. Pekerjaan fondasi yang tidak bergantung pada keputusan tersebut dapat berjalan paralel.

## 6. Workstream 1 — Bootstrap Proyek Laravel

### Tasks

- [ ] Buat repository dan branch strategy sesuai workflow tim.
- [ ] Pilih versi Laravel dan PHP yang supported serta kompatibel dengan hosting dan dependency.
- [ ] Siapkan Composer, Node/package manager dan asset build yang digunakan proyek.
- [ ] Pilih starter/authentication approach yang kompatibel dengan versi Laravel dan frontend baseline.
- [ ] Siapkan file `.env.example` tanpa secret; dokumentasikan seluruh konfigurasi wajib.
- [ ] Tambahkan formatter/linter PHP, test runner, static analysis yang realistis, dan aturan coding.
- [ ] Siapkan local development setup dan instruksi satu jalur yang jelas.
- [ ] Siapkan migration awal, factory, seeder, dan data uji sintetis.
- [ ] Siapkan error handling, structured logging, request/correlation ID dan masking data sensitif.
- [ ] Siapkan queue worker, cache, scheduler, object storage abstraction dan email transport sesuai environment.
- [ ] Siapkan CI untuk dependency install, lint/format check, static analysis yang dipilih, test suite, asset build dan pemeriksaan dependency.
- [ ] Siapkan environment development, staging dan production; jangan menggunakan credential production untuk local/staging.

### Definition of Done

- [ ] Engineer dapat menjalankan aplikasi lokal dari README tanpa instruksi informal.
- [ ] CI berjalan di setiap pull request dan memblokir kegagalan checks wajib.
- [ ] Tidak ada secret yang masuk source control atau log.
- [ ] Migration dan test dapat dijalankan dari database kosong.
- [ ] Deploy staging terdokumentasi dan dapat diulang.

## 7. Workstream 2 — Identity, Roles, dan Access Control

### Roles awal

- `customer`: melihat katalog, booking dan transaksi miliknya.
- `vendor`: mengelola listing, availability dan booking yang terkait dengan vendor sendiri.
- `admin`: verifikasi, moderasi, booking assisted, operasional, rekonsiliasi dan peninjauan transaksi/refund sesuai permission.
- `superadmin`: pengelolaan sistem dengan akses terbatas, terkontrol dan diaudit penuh.

Satu user dapat memiliki beberapa role jika model bisnis mengizinkan. Role tidak boleh ditentukan hanya dari field yang dikirim browser. Role `finance` ditiadakan; tugas keuangan/rekonsiliasi ditangani langsung oleh `admin` dan `superadmin`.

### Tasks

- [ ] Implementasikan register, login, logout, password reset dan verifikasi kontak sesuai kebutuhan produk.
- [ ] Implementasikan role/permission/policy dengan pemeriksaan server-side.
- [ ] Implementasikan profil customer dan profil vendor.
- [ ] Implementasikan workflow verifikasi vendor dan status akun.
- [ ] Lindungi file identitas/bukti dengan private storage dan authorization.
- [ ] Tambahkan rate limiting untuk login, reset akses, OTP bila digunakan, dan endpoint sensitif.
- [ ] Tambahkan audit log untuk perubahan permission, verifikasi, dan tindakan admin/finance.
- [ ] Pastikan customer/vendor tidak bisa mengakses record milik user/vendor lain dengan mengganti ID.

### Definition of Done

- [ ] Permission test mencakup akses diizinkan dan ditolak.
- [ ] Semua endpoint sensitif dilindungi, tidak hanya halaman UI.
- [ ] Reset password/token memiliki expiry dan tidak membocorkan apakah akun terdaftar.
- [ ] Dokumen privat tidak bisa diakses tanpa permission yang tepat.

## 8. Workstream 3 — Vendor, Lokasi, Katalog, dan Pencarian

### Tasks

- [ ] Buat model kategori, vendor, lokasi, listing, fasilitas, media, aturan dan jam operasi sesuai domain.
- [ ] Implementasikan listing lifecycle minimal: draft, submitted/review, published, rejected/archived sesuai keputusan produk.
- [ ] Implementasikan harga dan aturan penyewaan/penitipan dengan format yang dapat dijelaskan customer.
- [ ] Implementasikan kapasitas dan ketersediaan tanpa menjadikan listing publik sebagai jaminan slot tersedia.
- [ ] Implementasikan upload media: validasi extension/MIME, ukuran, dimensi bila relevan dan private/public access policy.
- [ ] Bangun halaman landing, search/filter, detail listing, profil vendor dan kebijakan.
- [ ] Terapkan pagination, sort/filter yang dibatasi, SEO basics, dan responsive layout.
- [ ] Sediakan loading, empty state, validation error dan server error state.
- [ ] Pastikan hanya listing approved dan eligible yang tampil ke customer.

### Definition of Done

- [ ] Vendor dapat mengelola listing sesuai status dan permission.
- [ ] Customer bisa memahami layanan, harga dan aturan sebelum booking.
- [ ] Search/filter memiliki hasil deterministik dan penanganan hasil kosong.
- [ ] Upload file aman dan listing tidak mengekspos dokumen internal.

## 9. Workstream 4 — Availability, Pricing, dan Booking Core

### Tasks

- [ ] Definisikan state machine booking dan tabel transisi yang disetujui.
- [ ] Buat server-side price quote dengan rincian harga, biaya dan currency.
- [ ] Simpan immutable snapshot harga pada saat booking dibuat.
- [ ] Implementasikan validasi periode, kapasitas dan race-condition handling melalui transaksi/locking yang sesuai.
- [ ] Implementasikan pembuatan booking dengan ID publik unik dan idempotency key bila relevan.
- [ ] Tentukan apakah availability di-hold sementara ketika payment pending; tentukan expiry dan release policy.
- [ ] Bangun checkout, halaman detail booking dan timeline yang menunjukkan actor/waktu/status.
- [ ] Implementasikan cancel/expiry sesuai policy; operasi yang berdampak pada uang memicu alur payment/refund, bukan mengubah record secara manual.
- [ ] Implementasikan booking berbantuan admin untuk permintaan yang datang dari WhatsApp/telepon jika kanal tersebut masih digunakan.
- [ ] Simpan `source_channel`, `created_by`, timestamp dan audit context.
- [ ] Sediakan pencarian booking admin dan filter status yang aman.

### Definition of Done

- [ ] Double-click/retry tidak menciptakan booking duplikat.
- [ ] Uji concurrent booking membuktikan kapasitas tidak terlampaui.
- [ ] Harga final selalu dihitung server-side.
- [ ] Customer hanya dapat melihat booking sendiri; vendor hanya melihat booking dalam cakupannya.
- [ ] Website booking dan assisted booking memakai domain logic serta lifecycle yang sama.

## 10. Workstream 5 — Payment Gateway Otomatis dan Settlement Langsung ke Vendor

Payment gateway dan penyaluran settlement ke vendor merupakan kebutuhan target MVP. Customer membayar melalui gateway dan dana ditargetkan disalurkan ke akun vendor melalui fitur marketplace/sub-merchant/split settlement provider. Jangan menggunakan rekening TitipSini sebagai penampung lalu payout ulang sebagai fallback otomatis. Implementasi provider tertentu baru dimulai setelah kemampuan, kontrak, dan model settlement diverifikasi.

### 10.1 Desain adapter

Definisikan kontrak internal yang tidak membawa istilah provider ke seluruh domain. Contoh operasi konseptual:

- membuat payment session/invoice/checkout reference dengan vendor/sub-merchant tujuan yang benar;
- membuat atau memeriksa status onboarding akun pembayaran vendor bila API provider mendukungnya;
- menerapkan pembagian nominal untuk komisi platform jika model bisnis dan provider mendukung split settlement;
- mengambil status pembayaran dan status settlement vendor dari provider;
- memverifikasi dan menormalisasi event webhook pembayaran serta settlement;
- mengajukan refund/reversal bila didukung, termasuk perilaku jika dana sudah disettle ke vendor;
- melakukan rekonsiliasi transaksi, fee, komisi dan settlement;
- memetakan kode/status provider ke status domain internal.

Implementasi provider diletakkan di adapter khusus. Domain Booking tidak boleh bergantung langsung pada SDK provider. Jangan membuat adapter generik yang berlebihan sebelum provider pertama diperlukan.

### 10.2 Alur pembayaran minimum

1. Customer memilih booking dan sistem melakukan validasi server-side.
2. Booking disimpan dalam status yang sesuai untuk menunggu pembayaran dan/atau persetujuan vendor, sesuai policy.
3. Server membuat payment attempt/reference lewat gateway yang dipilih, mengaitkannya dengan akun vendor/sub-merchant yang sudah aktif dan instruksi settlement yang telah disetujui.
4. Customer menyelesaikan pembayaran pada halaman/flow provider.
5. Browser redirect hanya digunakan untuk pengalaman pengguna; redirect **tidak** menjadi bukti final pembayaran.
6. Backend menerima webhook, memverifikasi signature/autentikasi sesuai dokumentasi resmi provider, serta memvalidasi amount, currency, merchant/sub-merchant reference, booking reference dan status. Event settlement vendor juga diproses jika disediakan provider.
7. Event diproses idempotently; status pembayaran dan booking diperbarui lewat aturan domain yang sah.
8. Timeline/audit/event analytics diperbarui dan notifikasi dimasukkan ke queue.
9. Rekonsiliasi berkala membandingkan payment, komisi/fee dan settlement ke vendor dengan data provider; selisih menghasilkan exception untuk ditinjau finance.

### 10.3 Model status payment yang disarankan

Status internal harus dinormalisasi, misalnya:

- `created`
- `pending`
- `paid`
- `failed`
- `expired`
- `cancelled`
- `refund_pending`
- `partially_refunded` bila didukung model bisnis
- `refunded`
- `disputed` atau `chargeback` bila tersedia dari provider

Daftar final mengikuti kemampuan gateway dan kebutuhan bisnis. Status pembayaran terpisah dari status booking. Catat payment attempts secara terpisah agar retry tidak menimpa histori sebelumnya.

### 10.4 Kontrol wajib

- [ ] Verifikasi webhook menggunakan mekanisme resmi provider dan validasi environment/merchant reference.
- [ ] Simpan event ID/provider reference dan cegah pemrosesan event yang sama lebih dari sekali.
- [ ] Validasi nominal, mata uang, merchant account dan hubungan payment-to-booking.
- [ ] Gunakan database transaction untuk mengubah payment/booking state secara konsisten.
- [ ] Simpan timestamp, status sebelumnya/baru, reason code, provider reference dan correlation ID.
- [ ] Jangan log API secret, token, full payment credential atau data pribadi yang tidak dibutuhkan.
- [ ] Tangani callback terlambat, tidak berurutan, duplikat, gagal dan tidak dikenal.
- [ ] Jika webhook gagal diproses, sediakan retry yang aman dan antrean exception untuk finance/engineering.
- [ ] Jangan menganggap pembayaran sukses hanya dari query parameter atau redirect browser.
- [ ] Jangan menandai paid secara manual tanpa permission khusus, alasan, bukti verifikasi, dan audit trail.
- [ ] Jangan menyimpan data kartu sensitif; gunakan hosted checkout/tokenization provider sesuai scope.
- [ ] Pastikan pembayaran sukses tidak digandakan akibat callback/retry.

### 10.5 Settlement langsung ke vendor, komisi, dan refund

Target aliran dana adalah customer membayar melalui gateway dan provider menyalurkan settlement ke akun vendor terkait. TitipSini tetap harus menyimpan catatan transaksi, fee, komisi (jika berlaku), status settlement, dan referensi provider untuk rekonsiliasi. Ini mensyaratkan provider yang secara eksplisit mendukung model marketplace/sub-merchant atau split settlement.

- [ ] Verifikasi dukungan provider untuk onboarding/KYC vendor, connected account/sub-merchant, settlement ke vendor, pemotongan komisi platform bila diperlukan, laporan settlement, dan webhook terkait.
- [ ] Definisikan siapa merchant of record, siapa menanggung fee gateway, bagaimana komisi platform dihitung, dan apakah komisi ditahan otomatis oleh provider atau ditagih dengan mekanisme lain yang disetujui.
- [ ] Verifikasi refund penuh/parsial dan reversal setelah settlement ke vendor; definisikan siapa yang harus menyediakan dana dan bagaimana exception ditangani jika reversal tidak bisa dilakukan otomatis.
- [ ] Simpan ledger/event keuangan append-only atau pola ekuivalen; koreksi menggunakan entry kompensasi, bukan mengedit histori secara diam-diam.
- [ ] Catat payment sukses dan settlement vendor sebagai status/record berbeda; pembayaran customer yang sukses tidak selalu berarti settlement vendor sudah selesai.
- [ ] Implementasikan approval control untuk refund dan tindakan keuangan sesuai risiko dan nilai transaksi.
- [ ] Buat rekonsiliasi untuk nominal, fee, komisi, settlement status, waktu settlement dan referensi provider yang tidak cocok.
- [ ] Jangan membuat transfer manual dari rekening TitipSini ke vendor sebagai fallback tanpa keputusan bisnis, prosedur compliance dan audit yang eksplisit.

### 10.6 Invarian settlement langsung ke vendor

- Akun vendor pada payment gateway harus berstatus aktif/terverifikasi sesuai persyaratan provider sebelum listing dapat menerima pembayaran online. Listing vendor yang belum siap dapat tetap disimpan sebagai draft, tetapi tidak boleh menerima checkout berbayar.
- Setiap payment attempt harus menunjuk ke vendor/sub-merchant yang benar dan menyimpan snapshot komisi/fee yang berlaku saat transaksi dibuat.
- Payment status (`paid`) dan vendor settlement status (`pending`, `settled`, `failed`, atau status ekuivalen provider) harus dipisahkan.
- Jika provider tidak mendukung settlement langsung untuk vendor/listing tersebut, checkout harus ditolak atau dinonaktifkan dengan pesan yang aman; sistem tidak boleh otomatis mengalihkan dana ke akun TitipSini.
- Perubahan beneficiary, split amount, komisi, atau settlement instructions harus divalidasi server-side dan diaudit.
- Refund setelah settlement perlu mengikuti kemampuan provider dan kebijakan yang disetujui; jangan menjanjikan refund otomatis sebelum reversal flow diuji di sandbox.

### Definition of Done payment

- [ ] Sandbox happy path dan failure path lulus.
- [ ] Signature invalid ditolak dan tidak mengubah status transaksi.
- [ ] Callback duplikat dan callback yang tidak berurutan tidak merusak status.
- [ ] Status final dikonfirmasi melalui webhook/API provider yang valid atau mekanisme rekonsiliasi yang disetujui.
- [ ] Amount/currency/reference mismatch masuk exception queue.
- [ ] Customer dapat melihat status yang dapat dipahami dan dapat mencoba membayar kembali bila aturan mengizinkan.
- [ ] Finance dapat menelusuri booking ↔ payment attempt ↔ provider reference ↔ vendor/sub-merchant ↔ settlement ↔ fee/komisi ↔ refund/ledger entry.

## 11. Workstream 6 — Portal Vendor dan Operasional Penitipan

### Tasks

- [ ] Bangun dashboard ringkasan listing, availability, booking baru dan booking berjalan.
- [ ] Implementasikan accept/reject dengan alasan serta batas waktu jika diputuskan.
- [ ] Implementasikan pengelolaan kapasitas/periode melalui validasi server-side.
- [ ] Implementasikan check-in, checklist barang/kondisi, waktu, actor dan bukti serah-terima.
- [ ] Implementasikan retrieval request/pengambilan dan check-out/completion.
- [ ] Buat akses customer pada bukti/status yang boleh dilihat; batasi catatan internal vendor.
- [ ] Tampilkan ringkasan nilai transaksi dengan membedakan gross booking, payment, fee, refund, dan payout.

### Definition of Done

- [ ] Hanya vendor terkait yang dapat mengubah booking atau listing.
- [ ] Setiap aksi operasional mencatat actor, waktu dan perubahan status.
- [ ] Bukti disimpan secara aman dan ditampilkan sesuai permission.
- [ ] Vendor dapat menyelesaikan alur operasional dari dashboard tanpa menjadikan chat sebagai catatan resmi.

## 12. Workstream 7 — Admin, Finance, Support, dan Audit

### Tasks

- [ ] Bangun antrean verifikasi vendor dan moderasi listing.
- [ ] Implementasikan assisted booking untuk permintaan dari kanal non-website; gunakan validasi dan lifecycle yang sama.
- [ ] Bangun dashboard pencarian dan detail booking dengan timeline.
- [ ] Bangun finance view untuk payment status, provider reference, rekonsiliasi dan exception.
- [ ] Implementasikan refund/payout sesuai scope dan approval policy yang disetujui.
- [ ] Bangun support ticket dengan kategori, prioritas, assignment, status dan hubungan ke booking.
- [ ] Implementasikan dispute case dan pembatasan tindakan keuangan bila diperlukan.
- [ ] Tambahkan laporan/ekspor yang dibatasi permission dan masking data.
- [ ] Terapkan separation of duties jika satu orang tidak boleh membuat dan menyetujui transaksi finansial yang sama.
- [ ] Catat perubahan sensitif dengan audit trail yang tidak dapat diedit oleh user biasa.

### Definition of Done

- [ ] Semua tindakan privileged memerlukan authorization backend dan menghasilkan audit entry.
- [ ] Assisted booking dapat dibuat, ditelusuri dan dihitung dalam metrik kanal.
- [ ] Finance dapat menindaklanjuti setiap payment mismatch.
- [ ] Tiket dapat dilacak dari pembuatan hingga penyelesaian.

## 13. Workstream 8 — Notification Service dan Keputusan WhatsApp

Peran WhatsApp masih **TBD**. Jangan mengintegrasikan WhatsApp secara langsung ke controller booking atau payment. Gunakan notification service dengan adapter agar keputusan kanal dapat diambil tanpa mengubah alur transaksi inti.

### Baseline MVP

- Status booking dan pembayaran selalu tersedia di halaman website.
- Sistem memiliki setidaknya kanal notifikasi transaksi yang diputuskan untuk MVP; rekomendasi awal adalah email transaksional dan/atau notifikasi di dalam dashboard.
- Event penting dipublikasikan dari domain dan diproses melalui queue/job.
- Pengiriman notifikasi gagal tidak boleh membatalkan booking atau pembayaran.
- Pesan tidak boleh menjadi sumber status baru; pengguna diarahkan kembali ke website.

### Jika WhatsApp kemudian dipilih

- [ ] Tentukan use case: notifikasi satu arah, reminder, customer support, atau interaksi dua arah.
- [ ] Pilih provider dan metode resmi yang sesuai; catat biaya, template approval, consent, rate limit, delivery webhook, dan data retention.
- [ ] Tentukan template untuk booking dibuat, pembayaran, keputusan vendor, pengingat, check-in/out, refund dan tiket.
- [ ] Link ke detail booking harus memerlukan login atau mekanisme secure, scoped, expiring link.
- [ ] Terapkan queue, retry/backoff, deduplication, delivery log dan alert.
- [ ] Simpan preference/consent dan sediakan fallback pada website/email bila perlu.
- [ ] Jangan mengirim dokumen identitas, informasi pembayaran sensitif atau data pribadi berlebihan.
- [ ] Jika akan menerima command dua arah, setiap command harus melalui autentikasi/validasi yang tepat dan tidak dapat memintas authorization atau state machine.

### Definition of Done

- [ ] Kanal notifikasi bisa diaktif/nonaktifkan melalui konfigurasi yang terdokumentasi.
- [ ] Domain booking/payment tidak bergantung pada keberhasilan provider notifikasi.
- [ ] Retry dan webhook delivery tidak menciptakan pesan atau tindakan domain duplikat.
- [ ] Jika WhatsApp belum disepakati, tidak ada integrasi/provider spesifik yang menjadi dependency release MVP.

## 14. Model Data Minimum

Nama berikut adalah rancangan domain awal; sesuaikan saat membuat migration berdasarkan keputusan bisnis. Hindari membuat semua tabel sekaligus sebelum relasi dan invarian disepakati.

| Entitas | Tujuan dan catatan |
|---|---|
| `users` | Identitas akun; unique identifier dan status akun. |
| `roles`, `permissions` atau ekuivalen | Hak akses; implementasikan sesuai kompleksitas dan starter approach yang dipilih. |
| `customer_profiles` | Data minimum customer yang dibutuhkan untuk booking. |
| `vendor_profiles` | Profil, status verifikasi dan status operasional vendor. |
| `locations` | Lokasi penitipan, jam operasi dan lokasi geografis bila dibutuhkan. |
| `categories` | Kategori barang/layanan. |
| `listings` | Deskripsi, kategori, harga, kapasitas, aturan, status publikasi. |
| `listing_media` | Referensi object storage, urutan dan metadata file. |
| `availability_slots` atau representasi kapasitas setara | Ketersediaan/periode serta mekanisme untuk mencegah overbooking. |
| `bookings` | ID publik, customer, vendor/listing, periode, status, `source_channel`, `created_by`. |
| `booking_price_snapshots` | Harga, biaya, diskon jika ada, total, currency, aturan harga saat transaksi dibuat. |
| `booking_events` | Timeline perubahan status, actor, waktu dan alasan. |
| `vendor_payment_accounts` | Referensi akun pembayaran/sub-merchant vendor pada provider, status onboarding/KYC, capabilities, dan timestamp. Simpan hanya identifier/status yang dibutuhkan; jangan simpan credential provider di record ini. |
| `payment_attempts` | Setiap percobaan pembayaran, provider, reference, amount, status, tujuan vendor/sub-merchant, idempotency key. |
| `payment_webhook_events` | Provider event ID, hash/reference payload yang aman, received/processed time, result. Jangan menyimpan secret atau data sensitif yang tidak diperlukan. |
| `vendor_settlements` | Referensi settlement provider, vendor/sub-merchant, gross, fee, komisi platform jika ada, net, status, settlement timestamp dan hasil rekonsiliasi. |
| `refunds` | Nilai, alasan, approval trail, status dan provider reference. |
| `ledger_entries` | Jejak keuangan/entry kompensasi yang dapat direkonsiliasi untuk pembayaran, fee, komisi, refund dan settlement vendor. |
| `vendor_payouts` | Hanya digunakan jika keputusan bisnis/provider membutuhkan transfer terpisah; bukan alur default untuk settlement langsung. |
| `handover_records` | Check-in/out, checklist, actor, timestamp dan referensi bukti. |
| `support_tickets` | Nomor tiket, booking terkait, kategori, status dan owner. |
| `notifications` | Event, channel, template, recipient ref, attempts, status dan error yang telah disanitasi. |
| `audit_logs` | Actor, action, entity, entity ID, timestamp dan perubahan relevan. |

### 14.1 Aturan data

- Gunakan primary key internal stabil dan ID publik booking yang tidak mudah ditebak.
- Uang menggunakan tipe numerik/decimal yang tepat dan currency eksplisit; hindari floating point untuk nominal uang.
- Timestamp disimpan dalam format konsisten (umumnya UTC) dan ditampilkan sesuai zona waktu bisnis/pengguna yang disepakati.
- Simpan snapshot harga/aturan saat booking terjadi.
- Jangan menghapus catatan pembayaran/audit karena akun/listing diarsipkan.
- PII dan dokumen identitas tidak dikirim ke analytics umum.
- Tambahkan foreign key, unique constraint, index, dan database transaction sesuai invarian domain.
- Pertimbangkan pola outbox bila konsistensi antara commit domain dan publikasi event/queue menjadi kebutuhan; jangan menambahkannya tanpa alasan yang terukur.

## 15. Status Domain dan Invarian

### 15.1 Booking state

Daftar awal yang perlu disahkan Product Owner:

- `pending_payment`
- `payment_review` bila ada kondisi khusus yang memerlukan pemeriksaan
- `awaiting_vendor` bila vendor harus menyetujui
- `confirmed`
- `checked_in` atau `stored`
- `retrieval_requested`
- `ready_for_return`
- `completed`
- `cancelled`
- `refund_pending`
- `refunded`
- `disputed`

Jangan memasukkan semua nilai tersebut secara otomatis bila tidak sesuai alur nyata. Buat transition table beserta role dan side-effect yang diperbolehkan.

### 15.2 Invarian backend

- Booking hanya dibuat untuk listing yang eligible dan periode/kapasitas yang valid.
- Ketersediaan di-hold/dilepas sesuai policy yang eksplisit.
- Payment tidak dianggap paid karena browser redirect.
- Status pembayaran hanya berubah melalui event provider yang valid, API provider yang tervalidasi, atau proses rekonsiliasi manual berpermission tinggi dan diaudit.
- Setiap status transition memvalidasi status awal, actor, permission dan alasan bila diperlukan.
- Setiap aksi kritis memiliki idempotency/deduplication sesuai konteks.
- Notifikasi gagal tidak me-rollback transaksi yang valid.
- Refund, komisi dan settlement vendor tidak menghapus histori transaksi; koreksi menghasilkan audit/ledger entry. Payment sukses dan settlement selesai merupakan fakta finansial yang berbeda.
- Status payment, booking, refund dan notification disimpan terpisah.

## 16. Routes dan Kontrak Aplikasi

Untuk MVP server-rendered Laravel, gunakan web routes/controllers/Form Requests/Policies sebagai default. API terpisah hanya dibuat bila ada kebutuhan nyata seperti frontend terpisah atau integrasi pihak ketiga.

Domain route minimum:

- **Public/catalog:** kategori, search/filter, listing detail, profil vendor, FAQ/kebijakan.
- **Auth/profile:** login/logout, reset password, profile, verifikasi kontak sesuai kebutuhan.
- **Customer:** quote/availability, create booking, payment initiation, booking list/detail, cancel sesuai policy, retrieval request, support ticket.
- **Vendor:** dashboard, listing CRUD, availability, booking queue, accept/reject, check-in/out.
- **Admin:** verification/moderation, assisted booking, booking lookup, support queue, audit view.
- **Finance:** payment lookup, reconciliation exceptions, refund/payout sesuai permission.
- **Payment webhook:** route terisolasi untuk provider callbacks, tidak memerlukan session customer tetapi harus memiliki verifikasi provider.
- **Internal/operations:** health checks, scheduler tasks, queue monitoring hanya untuk operator berwenang.

### Kontrak implementasi

- Gunakan Form Request atau mekanisme validasi setara untuk input.
- Gunakan Policies/Gates untuk akses resource.
- Return response error yang konsisten; jangan mengekspos stack trace, query, atau secret.
- Batasi pagination, sort dan filter; jangan mengembalikan seluruh dataset.
- Jangan mempercayai `role`, `vendor_id`, `price`, `total`, `payment_status`, atau `is_admin` dari payload client tanpa validasi ownership dan policy server.
- Semua webhook dicatat dengan correlation ID dan hasil pemrosesan tanpa mengekspos payload sensitif.

## 17. Work Breakdown Structure (WBS)

Prioritas: **P0** = wajib sebelum MVP; **P1** = setelah alur inti stabil. Ini adalah dependency order, bukan jadwal tanggal yang dijamin.

### Epic 0 — Product Decisions dan Solution Design

- [ ] Sahkan alur booking dan status domain.
- [ ] Pilih frontend approach dan database; dokumentasikan ADR.
- [x] Tetapkan target aliran dana ke vendor melalui payment gateway, bukan pengumpulan dana TitipSini untuk payout ulang.
- [ ] Pilih payment gateway yang terbukti mendukung direct-to-vendor/marketplace settlement, lalu dokumentasikan batasan dan kontraknya.
- [ ] Sahkan merchant-of-record, komisi platform, fee gateway, refund/reversal, dan rekonsiliasi settlement.
- [ ] Putuskan kanal notifikasi MVP dan status WhatsApp.
- [ ] Sahkan pricing, cancellation, refund, commission, settlement dan reversal rules.
- [ ] Tentukan kategori, lokasi dan vendor pilot.

**DoD:** keputusan blocker dicatat dengan owner dan dampak; asumsi yang belum selesai terlihat jelas di backlog.

### Epic 1 — Laravel Foundation (P0)

- [ ] Inisialisasi repository Laravel/PHP dan asset pipeline.
- [ ] Siapkan local/staging/production configuration.
- [ ] Tambahkan formatter/linter, testing, static analysis yang disepakati dan CI.
- [ ] Siapkan migrations, factories, seeders, logging, error handling dan health checks.
- [ ] Siapkan database, Redis/queue bila dipakai, storage dan email.

**DoD:** aplikasi dapat dijalankan dari setup kosong dan CI lulus.

### Epic 2 — Identity & Access (P0)

- [ ] Registrasi, login, reset akses, profil.
- [ ] Roles, permissions, policies, rate limits.
- [ ] Vendor verification dan audit log.

**DoD:** akses lintas customer/vendor ditolak oleh backend dan diuji.

### Epic 3 — Catalog & Vendor Listing (P0)

- [ ] CRUD kategori, lokasi, listing, media, pricing rules.
- [ ] Workflow moderation/publish.
- [ ] Public landing, catalog, search/filter dan listing detail.

**DoD:** customer bisa menemukan listing valid; vendor hanya mengelola datanya sendiri.

### Epic 4 — Availability & Booking Core (P0)

- [ ] Price quote, snapshot, availability validation dan hold/expiry jika digunakan.
- [ ] Booking ID, idempotency, timeline dan cancel rules.
- [ ] Assisted booking dengan source channel.

**DoD:** tidak ada overbooking di test konkurensi; booking web dan assisted memakai lifecycle sama.

### Epic 5 — Payment Gateway Integration (P0)

- [ ] Payment adapter dan satu implementasi provider yang mendukung settlement langsung ke vendor.
- [ ] Vendor/sub-merchant onboarding status dan validasi kelayakan sebelum menerima pembayaran.
- [ ] Sandbox checkout/initiation dengan tujuan settlement vendor yang benar.
- [ ] Verified webhook untuk pembayaran dan settlement, normalized statuses, idempotency dan retry.
- [ ] Rekonsiliasi report/exception queue untuk gross, fee, komisi, net dan status settlement.
- [ ] Refund/reversal flow yang mencakup keadaan sebelum maupun setelah settlement bila didukung.

**DoD:** happy path, failures, duplicate callback dan amount mismatch lulus; status resmi tidak tergantung redirect.

### Epic 6 — Vendor Operations (P0)

- [ ] Booking queue, keputusan vendor, availability management.
- [ ] Check-in/out, evidence upload, retrieval flow.

**DoD:** seluruh status operasional dilacak di website dan audit/timeline.

### Epic 7 — Admin, Finance, Support (P0)

- [ ] Moderation/verifikasi, assisted booking dan operational queue.
- [ ] Payment lookup, reconciliation exceptions, refund/payout controls sesuai scope.
- [ ] Support ticket dan dispute workflow.

**DoD:** staff dapat melakukan operasi utama tanpa spreadsheet/chat menjadi catatan transaksi resmi.

### Epic 8 — Notifications (P0 baseline; WhatsApp optional)

- [ ] Event-driven notification service dan queue.
- [ ] Email/in-app channel sesuai keputusan MVP.
- [ ] Adapter WhatsApp hanya setelah use case/provider disetujui.

**DoD:** notifikasi memiliki retry/deduplication/logging dan tidak menjadi dependensi status transaksi.

### Epic 9 — Security, QA, Analytics, Operations (P0)

- [ ] Unit/integration/feature/E2E test untuk flow kritis.
- [ ] Security/access review, upload review dan webhook review.
- [ ] Analytics tanpa PII berlebih, logs/alerts, backup/restore test.
- [ ] Staging UAT, runbook dan incident response.

**DoD:** tidak ada blocker kritis terbuka dan monitoring operasional tersedia.

### Epic 10 — Pilot dan Rollout (P0)

- [ ] Input/migrasikan data awal yang tervalidasi.
- [ ] Rehearsal data import/rollback jika diperlukan.
- [ ] Pilot pada vendor/kategori terbatas.
- [ ] Jalankan assisted booking untuk permintaan dari kanal lama.
- [ ] Pantau metrik, perbaiki masalah, perluas secara bertahap.

**DoD:** pilot disetujui berdasarkan data dan tidak ada transaksi aktif yang hilang atau tidak dapat ditelusuri.

## 18. Milestone dan Urutan Delivery

Jadwal durasi ditentukan setelah kapasitas tim, pemilihan gateway yang mendukung settlement langsung ke vendor, dan aturan komisi/refund/reversal diketahui. Jangan menjanjikan tanggal sebelum dependency tersebut diselesaikan.

| Milestone | Output | Exit criteria |
|---|---|---|
| M0 — Decisions ready | ADR frontend/database/payment/notifications; booking policy | Blocker untuk arsitektur dan transaksi memiliki keputusan/owner. |
| M1 — Laravel foundation | Repository, CI, environments, auth baseline | Setup dari nol dan CI lulus. |
| M2 — Supply & catalog | Vendor, lokasi, listing dan pencarian | Listing valid dapat dikelola dan ditemukan. |
| M3 — Booking core | Availability, price quote, booking ID, timeline | Concurrency dan idempotency tests lulus. |
| M4 — Payment connected | Gateway sandbox, webhook, normalized status, reconciliation | Payment tests termasuk duplicate/invalid webhook lulus. |
| M5 — Operations | Vendor portal, admin, handover dan support | Flow operasional utama dapat diselesaikan melalui website. |
| M6 — Connected MVP | Notification baseline, security, analytics, observability | E2E kritis dan UAT lulus. |
| M7 — Pilot | Data awal, SOP, pilot vendor dan monitoring | Owner produk menerima hasil pilot. |
| M8 — Website-first rollout | Perluasan adopsi dan perbaikan berkelanjutan | Operasi stabil selama periode yang disepakati. |

### Sprint/batch awal yang disarankan

| Batch | Fokus | Hasil utama |
|---|---|---|
| 0 | Product decisions dan technical design | ADR, status rules, direct-to-vendor payment decision, initial backlog |
| 1 | Laravel bootstrap dan identity | App, CI, auth, RBAC/policies, audit baseline |
| 2 | Vendor/listing dan katalog publik | Listing CRUD, moderation, search/detail |
| 3 | Availability, pricing dan booking | Booking core, state machine, tests |
| 4 | Payment gateway | Adapter, sandbox, webhook, reconciliation |
| 5 | Vendor operations dan admin | Booking queue, handover, assisted booking, support |
| 6 | Notifications, finance refinements dan hardening | Notification adapter, refund scope, analytics, security |
| 7 | UAT, pilot dan rollout | Runbook, data import, pilot feedback dan fixes |

Batch bukan komitmen jumlah sprint. Pecah menjadi issue terukur dan atur urutannya sesuai dependency aktual.

## 19. Testing Strategy

### 19.1 Unit tests

- Kalkulasi harga dan snapshot.
- Booking state machine serta transisi terlarang.
- Availability, capacity dan expiry.
- Policies/permissions.
- Mapping status gateway ke payment status internal.
- Webhook signature result dan idempotency handling.
- Refund/cancellation rules yang telah disahkan.
- Event-to-notification mapping.

### 19.2 Integration/feature tests

- Transaction database saat membuat booking dan mengunci kapasitas.
- Payment gateway sandbox dan contract tests untuk adapter.
- Webhook valid, invalid, duplicate, late dan out-of-order.
- Queue retry, failed job, notification deduplication.
- File upload dan private access policy.
- Reconciliation untuk data internal vs provider.
- Authorization seluruh role pada resource penting.

### 19.3 End-to-end tests wajib

1. Customer mencari listing, melihat detail, meminta quote, membuat booking dan melihat status.
2. Dua request bersamaan mencoba mengambil kapasitas terbatas; sistem tidak oversell.
3. Payment berhasil dan booking berubah hanya setelah konfirmasi backend yang valid.
4. Payment attempt diarahkan ke akun vendor/sub-merchant yang tepat dan status settlement vendor dapat direkonsiliasi.
5. Vendor dengan akun payment gateway belum aktif tidak dapat menerima checkout berbayar.
6. Jika fitur direct-to-vendor settlement tidak tersedia, sistem tidak mengalihkan dana ke akun TitipSini secara diam-diam.
7. Payment gagal/expired lalu customer mencoba lagi sesuai policy tanpa payment record rusak.
8. Redirect sukses tetapi webhook belum diterima; UI tidak mengklaim paid secara prematur.
9. Webhook duplicate/invalid tidak menggandakan transaksi atau mengubah status.
10. Provider mengirim amount/reference yang tidak cocok; event masuk exception queue.
11. Vendor menerima/menolak booking, melakukan check-in dan menyelesaikan check-out.
12. Admin membuat assisted booking dari lead WhatsApp tanpa integrasi WhatsApp sekalipun.
13. Customer A tidak bisa mengakses booking Customer B; Vendor A tidak bisa mengakses booking Vendor B.
14. Staff tanpa permission finance tidak bisa melakukan refund/override finansial yang tak diizinkan.
15. Notifikasi gagal dikirim tetapi status transaksi tetap konsisten.

### 19.4 UAT

UAT minimal melibatkan perwakilan customer, vendor, admin operasional dan finance. Gunakan data uji sintetis; catat hasil, severity, owner, keputusan dan sign-off. Payment tests harus dilakukan di sandbox sebelum production credentials diaktifkan.

## 20. Security, Privacy, dan Reliability Checklist

- [ ] Gunakan mekanisme auth/session Laravel yang sesuai dan dependency yang supported.
- [ ] Semua endpoint/action memeriksa authorization di backend; gunakan Policies/Gates sesuai domain.
- [ ] Terapkan validasi input, CSRF protection pada form web, rate limiting dan secure headers yang sesuai.
- [ ] Cegah IDOR dengan pemeriksaan ownership/permission setiap resource.
- [ ] Validasi upload file, simpan dokumen privat, dan gunakan signed URL terbatas bila diperlukan.
- [ ] Secret berada di environment/secret manager, bukan repository atau prompt AI.
- [ ] Jangan menyimpan data kartu sensitif; gunakan hosted checkout/tokenization provider.
- [ ] Verifikasi webhook sesuai dokumentasi gateway dan gunakan idempotency.
- [ ] Redact token, secret, PII dan data sensitif dari log/error reporting.
- [ ] Audit tindakan sensitif seperti verifikasi, override status, refund, payout dan permission changes.
- [ ] Batasi akses finance/admin sesuai least privilege; pertimbangkan maker-checker.
- [ ] Siapkan backup otomatis, uji restore, monitoring error, queue failed jobs dan alert payment mismatch.
- [ ] Dokumentasikan retensi data, consent kanal, penghapusan akun dan akses export data sesuai kebijakan yang disetujui.
- [ ] Review dependency/security advisories sebelum rilis.

## 21. Initial Data Setup (Build from Zero)

Karena aplikasi dibangun dari nol, **tidak ada migrasi aplikasi lama secara otomatis**. Namun, data operasional awal dapat berasal dari spreadsheet, catatan admin atau booking aktif yang masih dikelola manual.

### Data yang mungkin dimuat

1. Vendor dan lokasi yang telah diverifikasi.
2. Listing, foto, harga, kapasitas dan aturan yang telah disetujui.
3. Akun customer hanya jika diperlukan dan tersedia dasar pengelolaan yang sesuai; bila tidak, biarkan customer membuat akun baru.
4. Booking mendatang/aktif dan kewajiban transaksi yang belum selesai, setelah rekonsiliasi manual.
5. Tiket/sengketa aktif yang perlu ditindaklanjuti.

Jangan mengimpor semua percakapan WhatsApp secara default.

### Runbook input data

- [ ] Tentukan sumber data, owner, field mapping dan aturan deduplication.
- [ ] Bersihkan field wajib, format tanggal, nominal uang, kontak dan relasi vendor/listing.
- [ ] Tandai record yang perlu validasi owner sebelum dimasukkan.
- [ ] Lakukan import rehearsal ke staging.
- [ ] Rekonsiliasi jumlah record dan nominal keuangan; periksa sampel record.
- [ ] Dokumentasikan record yang ditolak/bermasalah.
- [ ] Import data produksi dengan persetujuan owner dan backup.
- [ ] Verifikasi hasil setelah import.

### Booking assisted selama transisi

- Admin boleh membuat booking dari permintaan WhatsApp/telepon melalui dashboard.
- Setiap booking harus memiliki `source_channel`, `created_by`, customer/contact ref, listing/vendor, periode, price snapshot dan catatan relevan.
- Periksa duplikasi dan availability sebelum membuat booking.
- Pembayaran booking assisted harus tetap dibuat dan ditelusuri melalui payment flow resmi yang sama; jangan membuat status `paid` berdasarkan pernyataan chat.
- Status booking resmi selalu diperbarui pada website, dan pesan kanal lain hanya memberitahukan status atau mengarahkan pengguna ke website.

## 22. Analytics dan KPI

### Metrik operasional minimum

- Jumlah booking per hari/minggu dan per status.
- Proporsi booking website vs assisted/kanal lainnya.
- Persentase lead kanal non-website yang tercatat sebagai booking resmi.
- Booking conversion dan search no-result rate.
- Waktu dari booking dibuat ke keputusan vendor.
- Payment success/failure/expiry dan retry success.
- Payment reconciliation exceptions dan nilai mismatch.
- Refund count/amount/time to resolve.
- Check-in/check-out completion.
- Ticket count dan median resolution time.
- Notification delivery/failure serta queue backlog.
- Error rate, latency, failed jobs dan availability aplikasi.

### Prinsip analytics

- Instrumentasi event pada domain boundary yang benar, bukan hanya click tracking.
- Jangan mengirim PII, dokumen identitas, token pembayaran atau isi percakapan ke analytics umum.
- Definisikan event name, properties, owner dan retention.
- Ambil baseline sebelum menetapkan target peningkatan. Target numerik harus disetujui Product Owner.

## 23. Deployment, Release, dan Rollout

### 23.1 Environment

- Development: data sintetis dan credential sandbox.
- Staging: konfigurasi mirip production, gateway sandbox, tes migration/restore dan UAT.
- Production: secret terkelola, HTTPS, database backup, queue worker/scheduler, logging, alert dan access control.

### 23.2 Go-live checklist

- [ ] Seluruh flow customer/vendor/admin/finance kritis lulus UAT.
- [ ] Payment gateway production sudah disetujui dan kredensial dipasang secara aman.
- [ ] Webhook endpoint dapat dijangkau, signature validation diuji dan event monitoring aktif.
- [ ] Rekonsiliasi payment provider tersedia dan owner exception jelas.
- [ ] Tidak ada blocker security atau defect kritis tanpa mitigasi yang disetujui.
- [ ] Backup, restore dan rollback sudah diuji di staging.
- [ ] Queue worker, scheduler, failed job alerts dan logs terpantau.
- [ ] Vendor/customer pilot memperoleh panduan dan jalur bantuan.
- [ ] Data awal/booking aktif telah diverifikasi bila ada import.
- [ ] Runbook insiden: payment mismatch, gateway down, queue down, data issue, rollback tersedia.
- [ ] Keputusan WhatsApp tidak menghambat transaksi inti; jika integrasi belum dipilih, release notes menyebutkan bahwa notifikasi alternatif tersedia.

### Kondisi no-go

Jangan melakukan rollout penuh bila payment success belum dapat diverifikasi di backend, nominal/reference tidak dapat direkonsiliasi, booking dapat oversell, akses antar-customer/vendor belum aman, atau backup/rollback belum terbukti.

## 24. Panduan untuk AI Coding Agent

Baca file ini bersama `PRD_TitipSini_WebTerintegrasi.md`. Proyek dimulai dari nol dengan Laravel/PHP; jangan mencoba mencari atau mengubah aplikasi lama yang tidak ada.

### Sebelum implementasi

- Periksa versi Laravel/PHP yang dipilih, `composer.json`, `package.json` jika digunakan, route, migration, config dan tests yang ada.
- Buat ringkasan rencana file yang akan dibuat/diubah sebelum melakukan perubahan besar.
- Periksa keputusan terbuka dan jangan mengarang business rule pembayaran, refund, payout, komisi atau WhatsApp.
- Jangan memasang package baru tanpa mengevaluasi maintenance, kompatibilitas dan alasan kebutuhan.
- Jangan menambahkan SPA, microservices atau abstraction yang tidak diperlukan untuk MVP.

### Saat implementasi

- Kerjakan satu issue kecil yang memiliki acceptance criteria dan dependency jelas.
- Gunakan Laravel conventions, dependency injection, Form Requests, Policies/Gates, migrations dan jobs yang sesuai.
- Business logic transaksi berada di domain/service/action, bukan hanya di controller/view.
- Tambahkan test bersama implementasi.
- Gunakan sandbox/test fixtures; jangan memasukkan secret atau data produksi ke test/prompt.
- Jangan menandai payment integration selesai sebelum webhook, idempotency dan reconciliation tests lulus.
- Jangan menandai WhatsApp selesai/required sebelum provider dan use case disetujui.
- Jangan melakukan destructive migration tanpa backup, plan, dan approval.
- Update dokumentasi dan decision log ketika keputusan atau perilaku berubah.

### Format issue yang disarankan

```md
## Issue: [ID] Judul singkat
Epic: [nama epic]
Priority: P0 | P1

### Context
Kebutuhan produk dan rujukan PRD/Implementation Plan.

### Scope
Perubahan yang termasuk dan batasan.

### Dependencies
Issue dan keputusan yang harus selesai terlebih dahulu.

### Acceptance Criteria
- [ ] Kriteria yang dapat diverifikasi

### Tests
- [ ] Unit
- [ ] Feature/integration
- [ ] E2E/manual QA yang relevan

### Out of Scope
Fitur yang tidak boleh ikut dibuat.
```

### Format laporan hasil implementasi

```md
## Implementation Report
- Summary:
- Files changed:
- Migration/schema changes:
- Routes/API changes:
- Payment/provider changes (if any):
- Tests run and results:
- Acceptance criteria status:
- Security considerations:
- Known limitations:
- Open decisions:
- Recommended next issue:
```

## 25. Decision Register

Keputusan yang masih terbuka tidak menghalangi seluruh pekerjaan. Lanjutkan task yang independen dan tandai task yang membutuhkan keputusan.

| ID | Keputusan | Status | Dampak |
|---|---|---|---|
| DEC-01 | Pilih frontend: Blade + Livewire (rekomendasi awal) atau Inertia/Vue/React | Open | Struktur UI, build pipeline, cara test browser dan kebutuhan interaktivitas. |
| DEC-02 | Pilih database relasional untuk production | Open | Schema, migration, locking dan operasional. |
| DEC-03 | Pilih payment gateway provider yang mendukung direct-to-vendor/marketplace settlement | Open — blocker untuk provider-specific integration | Checkout, vendor onboarding/KYC, webhook pembayaran/settlement, refund/reversal, fee dan konfigurasi production. |
| DEC-04 | Target aliran dana: customer membayar melalui gateway dan settlement masuk ke akun vendor; jangan dialihkan ke akun TitipSini tanpa keputusan baru | Target model dipilih; dukungan provider harus divalidasi sebelum implementasi final | Perlu akun vendor/sub-merchant, pembagian komisi bila ada, ledger, refund dan rekonsiliasi settlement. |
| DEC-05 | Peran WhatsApp: notifikasi, support, dua arah atau tidak masuk MVP | Open — bukan blocker untuk core booking | Provider, template, consent, biayanya dan scope integrasi. |
| DEC-06 | Booking otomatis atau harus menunggu persetujuan vendor | Open | State machine, hold capacity, expiry dan refund/cancel policy. |
| DEC-07 | Rumus harga, durasi, komisi, biaya dan pajak | Open | Price quote, snapshot, laporan dan rekonsiliasi. |
| DEC-08 | Aturan pembayaran pending, pembatalan, refund dan expiry | Open | Payment/booking lifecycle dan pengalaman customer. |
| DEC-09 | Apakah provider settlement langsung memenuhi kebutuhan operasional atau masih diperlukan proses transfer/payout tambahan | Open — bergantung pada kemampuan provider dan kontrak | Menentukan scope finance; tidak boleh ada transfer tambahan default jika settlement langsung ke vendor sudah didukung. |
| DEC-10 | Kategori/area dan jumlah vendor untuk pilot | Open | Scope katalog, konfigurasi dan UAT. |
| DEC-11 | Sumber data awal dan booking aktif yang harus dimasukkan | Open | Data setup, deduplication, rekonsiliasi dan dukungan operasional. |
| DEC-12 | Hosting, email provider, storage, Redis/queue dan monitoring | Open | Deployment, reliability dan biaya operasional. |

## 26. Definition of Done Global

Fitur dianggap selesai jika:

1. Requirement dan acceptance criteria sesuai PRD/issue.
2. UI memiliki loading, empty, success, validation error, permission error dan system error states yang relevan.
3. Validation dan authorization dilakukan di server.
4. Unit/feature/integration/E2E test yang relevan tersedia dan lulus.
5. Audit log/event logging tersedia untuk tindakan yang sesuai.
6. Security dan privacy diperiksa, termasuk redaction log dan permission.
7. Responsif pada perangkat target dan alur dapat diakses.
8. Dokumentasi konfigurasi/API/operasional diperbarui.
9. Migration aman untuk kondisi database dan rencana deployment.
10. Fitur diuji di staging sebelum rilis.
11. Integrasi payment tidak bergantung pada redirect browser; webhook dan reconciliation diuji.
12. Fitur WhatsApp tidak menjadi dependency core selama keputusan kanal belum dibuat.

## 27. Referensi Internal

- `PRD_TitipSini_WebTerintegrasi.md` — sumber requirement produk.
- `docs/product/as-is-to-be.md` — target proses bisnis.
- `docs/decisions/ADR-001-architecture.md` — keputusan arsitektur/frontend/database.
- `docs/decisions/ADR-002-payment-gateway.md` — keputusan gateway dan aliran dana.
- `docs/decisions/ADR-003-notifications-whatsapp.md` — keputusan notifikasi dan WhatsApp.
- `docs/open-questions.md` — keputusan yang belum selesai.

---

**Status dokumen:** Draft 2.1. Rencana ini mengasumsikan build from zero dengan Laravel/PHP serta target pembayaran melalui payment gateway dengan settlement langsung ke akun vendor. Provider wajib mendukung fitur marketplace/sub-merchant atau split settlement yang diperlukan. Sebelum integrasi provider-specific dimulai, validasi kapabilitas, kontrak, onboarding vendor, komisi, refund/reversal, webhook settlement, dan rekonsiliasi. Peran WhatsApp tetap fleksibel dan tidak menjadi dependency booking atau pembayaran.
