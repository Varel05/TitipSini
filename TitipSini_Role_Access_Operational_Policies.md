---
title: "TitipSini — Role, Access, and Operational Policies"
document_type: "Product / Business Rules Specification"
version: "1.0-draft"
status: "Draft for validation"
language: "id-ID"
last_updated: "2026-10-28"
related_documents:
  - "PRD_TitipSini_WebTerintegrasi.md"
  - "IMPLEMENTATION_PLAN_TitipSini.md"
  - "ADR-002-payment-gateway.md"
---

# TitipSini — Role, Access, and Operational Policies

## 1. Tujuan dokumen

Dokumen ini merangkum keputusan hasil diskusi mengenai role, hak akses, struktur wilayah, operasional booking dan vendor, pembayaran, sengketa, serta aturan bisnis TitipSini. Dokumen ini ditujukan sebagai konteks bagi Product Owner, designer, backend/frontend engineer, QA, dan AI coding agent.

Dokumen ini adalah **baseline draft**. Keputusan yang sudah disepakati ditulis sebagai aturan produk sementara; ketidakjelasan integrasi atau konflik aturan ditandai pada bagian **Open Decisions** dan tidak boleh ditebak oleh implementasi.

## 2. Prinsip dasar

1. Website merupakan sumber data resmi untuk booking, status layanan, pembayaran, vendor, lokasi, sengketa, dan audit.
2. WhatsApp tidak digunakan sebagai bagian dari workflow sistem.
3. Hak akses mengikuti prinsip least privilege: pengguna hanya dapat mengakses data dan tindakan yang diperlukan untuk role, wilayah, lokasi, booking, atau kasus yang menjadi tanggung jawabnya.
4. Satu wilayah operasional hanya memiliki satu Admin aktif.
5. Superadmin mengendalikan akun Admin dan kebijakan global; Admin menangani operasional harian di wilayahnya.
6. Status pembayaran hanya dapat berubah berdasarkan konfirmasi resmi provider atau proses sistem yang tervalidasi. Admin maupun Vendor tidak boleh menandai pembayaran berhasil secara manual.
7. Semua tindakan sensitif harus dapat ditelusuri. Audit log internal hanya dapat diakses Superadmin.
8. Perubahan kebijakan global berlaku untuk booking baru; booking yang sudah dibuat mempertahankan snapshot kebijakan pada saat booking dibuat.
9. Pembayaran customer dilakukan di akhir layanan, saat customer ingin mengambil barang. Tidak ada pembayaran saat booking.
10. Integrasi pembayaran tahap awal menggunakan Xendit dan QRIS saja, dengan dukungan marketplace/split settlement wajib dikonfirmasi sebelum produksi.

## 3. Role dan tanggung jawab

### 3.1 Superadmin

Superadmin adalah pusat kendali platform lintas wilayah dan pemilik pengaturan global.

Tanggung jawab dan hak akses:
- Membuat, mengubah, menonaktifkan, mengaktifkan kembali, dan mengelola akses akun Admin.
- Menetapkan dan memindahkan penugasan wilayah Admin.
- Memastikan hanya ada satu Admin aktif pada suatu wilayah.
- Mengambil alih seluruh tugas Admin saat wilayah tidak memiliki Admin aktif.
- Mengelola kebijakan global melalui dashboard, termasuk komisi, kategori layanan, model tarif yang tersedia, masa tenggang SKCK, jadwal pengingat, kategori risiko, dan kategori sengketa wajib eskalasi.
- Memantau semua wilayah, vendor, lokasi, booking, pembayaran, payout, sengketa, dan rekonsiliasi.
- Menangani rekonsiliasi, koreksi finansial yang diizinkan, payout, serta kasus finansial/risiko tinggi.
- Memutuskan penangguhan atau pembatasan seluruh akun vendor jika ada risiko lintas lokasi.
- Menyetujui pencabutan pembatasan berisiko tinggi.
- Menyetujui permintaan Admin untuk melihat dokumen SKCK lengkap.
- Melihat seluruh audit log.
- Menetapkan aturan dan formula kompensasi standar.

### 3.2 Admin wilayah

Admin bertanggung jawab atas operasional harian di satu wilayah yang ditetapkan Superadmin.

Tanggung jawab dan hak akses:
- Mengelola operasional booking di wilayahnya, termasuk membantu koreksi detail sesuai status booking, pembatalan khusus, dan koordinasi kendala.
- Memeriksa dokumen pendaftaran vendor, SKCK, serta persyaratan identitas.
- Menyetujui atau menolak vendor setelah verifikasi.
- Memverifikasi dan menyetujui lokasi vendor di wilayahnya.
- Meminta perbaikan dokumen yang kurang/salah; menolak pendaftaran jika syarat wajib tidak terpenuhi atau ada masalah serius.
- Memantau vendor, lokasi, listing, booking, dan customer yang terkait dengan wilayahnya.
- Meminta vendor memperbaiki listing; menonaktifkan lokasi untuk pelanggaran operasional biasa.
- Menangani sengketa operasional biasa dan mengoordinasikan bukti dari customer/vendor.
- Membantu customer ketika pembayaran bermasalah, memeriksa status transaksi, dan membuka kembali siklus pembayaran QRIS sesuai aturan.
- Memverifikasi perubahan rekening bank vendor setelah status verifikasi dari Xendit tersedia.
- Mengajukan kasus berisiko tinggi atau kasus wajib eskalasi kepada Superadmin.
- Mengaktifkan kembali lokasi yang dinonaktifkan karena pelanggaran operasional biasa setelah masalah diperbaiki.
- Mengoreksi data administratif vendor berdasarkan bukti, dengan alasan wajib.
- Memicu pemeriksaan ulang perhitungan biaya; tidak boleh memasukkan nominal pengganti secara manual.

Batasan Admin:
- Tidak dapat mengakses audit log internal.
- Tidak dapat melihat SKCK lengkap secara langsung. Admin melihat status verifikasi; akses lengkap memerlukan permintaan dan persetujuan Superadmin.
- Tidak dapat mengubah status pembayaran menjadi berhasil secara manual.
- Tidak dapat melakukan rekonsiliasi finansial atau mengendalikan payout sebagai pemilik proses.
- Tidak dapat mengubah nominal biaya penitipan, komisi, atau total transaksi secara manual.
- Tidak dapat mengubah kebijakan global.
- Tidak dapat menangguhkan seluruh akun vendor secara final pada kasus risiko tinggi; Admin mengajukan kasus dan Superadmin menentukan hasil akhir.
- Tidak dapat mengaktifkan kembali lokasi yang dibatasi berdasarkan keputusan risiko tinggi Superadmin.
- Tidak dapat melihat data customer lintas wilayah kecuali melalui mekanisme akses khusus yang belum ditentukan.

### 3.3 Vendor (individu)

Vendor adalah individu yang menyediakan layanan penitipan dan mengelola lokasi miliknya sendiri.

Tanggung jawab dan hak akses:
- Mendaftar dan melengkapi identitas serta SKCK.
- Menyelesaikan onboarding pembayaran Xendit yang dibutuhkan sebelum dapat menerima booking.
- Mengelola profil, listing, deskripsi, harga, kapasitas, alamat, fasilitas, layanan, dan jam operasional miliknya sendiri.
- Memiliki satu atau beberapa lokasi penitipan. Setiap lokasi ditetapkan ke wilayah operasional sesuai alamat dan diverifikasi oleh Admin wilayah terkait.
- Memilih model tarif yang diizinkan Superadmin untuk kategori layanan; vendor menentukan nominal harga sendiri tanpa batas minimum/maksimum platform yang ditetapkan saat ini.
- Menerima atau menolak booking.
- Mengonfirmasi penerimaan barang, memulai layanan, menandai barang siap diambil, dan mengonfirmasi pengembalian barang.
- Melihat informasi customer hanya dalam lingkup booking yang ditanganinya dan sesuai status booking.
- Mengajukan perubahan rekening bank melalui mekanisme Xendit; rekening baru tidak digunakan sebelum verifikasi provider dan persetujuan Admin selesai.
- Mengajukan penjelasan atau bukti saat sengketa.

Batasan Vendor:
- Hanya dapat mengelola profil dan lokasi miliknya sendiri.
- Tidak dapat menyetujui atau memverifikasi lokasi sendiri.
- Tidak dapat memanipulasi status pembayaran atau payout.
- Tidak dapat menerima booking baru jika persyaratan aktivasi belum lengkap, lokasi nonaktif, SKCK melewati masa tenggang tanpa pembaruan terverifikasi, atau vendor dibatasi akibat risiko.

### 3.4 Customer

Customer adalah pengguna yang mencari layanan, membuat booking, menitipkan barang, membayar pada akhir layanan, dan mengambil kembali barang.

Hak dan tanggung jawab:
- Mencari dan membandingkan lokasi/layanan yang aktif.
- Membuat booking tanpa melakukan pembayaran di muka.
- Melihat status booking dan informasi layanan terkait.
- Membatalkan booking sebelum barang dititipkan.
- Meminta pengakhiran layanan dan pengambilan barang lebih awal kapan saja setelah layanan dimulai.
- Membayar saat hendak mengambil barang melalui QRIS/Xendit.
- Melihat rincian biaya penitipan, kompensasi (jika ada), biaya gateway, dan total pembayaran sebelum membayar.
- Mengonfirmasi penerimaan barang setelah barang dikembalikan.
- Mengajukan komplain/sengketa.

## 4. Struktur wilayah dan batas data

### 4.1 Pembentukan wilayah

- Wilayah operasional fleksibel, tetapi dibentuk menggunakan satu atau beberapa wilayah administratif dari daftar sistem (provinsi, kota/kabupaten, kecamatan).
- Superadmin menentukan kombinasi wilayah administratif pembentuk suatu wilayah operasional.
- Setiap wilayah operasional memiliki tepat satu Admin aktif pada satu waktu.
- Satu Admin tidak dapat bertugas di dua wilayah pada waktu yang sama.
- Sistem perlu mencegah atau mendeteksi wilayah administratif yang tumpang tindih. Aturan penyelesaian tumpang tindih perlu diputuskan sebelum implementasi geo-assignment.

### 4.2 Penugasan vendor dan lokasi

- Satu akun vendor dapat memiliki beberapa lokasi di wilayah berbeda.
- Setiap lokasi memiliki alamat, kategori, fasilitas, kapasitas, model tarif/harga, jam operasional, status verifikasi, dan status operasional sendiri.
- Wilayah lokasi ditentukan dari alamat administratif yang dipilih/diverifikasi.
- Admin wilayah lokasi bertanggung jawab atas verifikasi dan operasional lokasi tersebut.
- Verifikasi identitas vendor dan SKCK dilakukan satu kali pada level akun vendor.
- Setiap lokasi tambahan harus diverifikasi oleh Admin wilayah terkait untuk alamat, fasilitas, kapasitas, dan persyaratan lokasi tanpa mengulang seluruh verifikasi identitas vendor.

### 4.3 Pengambilalihan wilayah

- Jika Admin tidak aktif atau dinonaktifkan, wilayah berubah menjadi **Dalam Pengawasan Superadmin**.
- Superadmin dapat melakukan seluruh fungsi Admin sementara.
- Semua tindakannya diberi penanda khusus takeover dan tercatat sebagai aktivitas Superadmin.
- Data vendor, lokasi, customer, booking, dan transaksi tetap berada di wilayah semula; tidak dipindahkan.
- Setelah Admin baru ditetapkan, tanggung jawab operasional kembali ke Admin tersebut.

## 5. Matriks hak akses ringkas

| Kapabilitas | Superadmin | Admin wilayah | Vendor | Customer |
|---|---|---|---|---|
| Mengelola akun Admin | Penuh | Tidak | Tidak | Tidak |
| Menentukan wilayah/Admin | Penuh | Tidak | Tidak | Tidak |
| Mengubah kebijakan global | Penuh | Tidak | Tidak | Tidak |
| Melihat data lintas wilayah | Penuh | Tidak, kecuali akses khusus yang disetujui | Lokasi milik sendiri saja | Booking sendiri |
| Verifikasi identitas/SKCK vendor | Pengawasan/akses penuh sesuai kebutuhan | Verifikasi operasional; SKCK penuh perlu persetujuan Superadmin | Mengunggah dokumen sendiri | Tidak |
| Menyetujui vendor | Dapat intervensi | Ya, di wilayahnya | Tidak | Tidak |
| Menyetujui lokasi | Pengawasan/intervensi | Ya, untuk wilayah lokasi | Tidak | Tidak |
| Mengelola listing/harga sendiri | Kebijakan global | Meminta koreksi/menonaktifkan listing sesuai kewenangan | Ya | Tidak |
| Mengelola booking | Lintas wilayah/intervensi | Penuh di wilayahnya dengan batas finansial | Booking lokasi sendiri | Booking sendiri |
| Menandai pembayaran berhasil manual | Tidak; status dari provider/sistem | Tidak | Tidak | Tidak |
| Membantu kasus pembayaran | Pengawasan/rekonsiliasi/payout | Ya, operasional | Melihat status terkait | Membayar/melihat status sendiri |
| Rekonsiliasi dan payout | Penanggung jawab | Tidak | Melihat status payout sendiri | Tidak |
| Mengubah nominal tagihan manual | Tidak sebagai tindakan bebas; koreksi harus terkontrol | Tidak | Tidak | Tidak |
| Menangani sengketa operasional biasa | Pengawasan/eskalasi | Ya | Menyediakan bukti | Mengajukan bukti/komplain |
| Memutus kasus wajib eskalasi | Ya | Mengumpulkan/meneruskan bukti | Menyediakan bukti | Menyediakan bukti |
| Menonaktifkan lokasi biasa | Dapat intervensi | Ya, dengan alasan | Tidak | Tidak |
| Memutus risiko tinggi akun/lokasi | Ya | Menandai/mengajukan; sistem dapat membatasi booking baru | Tidak | Tidak |
| Melihat audit log internal | Semua wilayah | Tidak | Tidak | Tidak |
| Mengakses kontak pihak lain | Untuk tugas sah sesuai akses | Untuk booking di wilayahnya sesuai kebutuhan | Kontak customer hanya setelah booking diterima dan selama booking aktif | Kontak vendor setelah booking diterima dan selama booking aktif |

Matriks ini adalah ringkasan. Implementasi permission harus dipecah lagi menjadi permission granular dan diuji pada API/backend, bukan hanya menyembunyikan tombol di UI.

## 6. Verifikasi vendor dan SKCK

### 6.1 Status verifikasi vendor

Alur yang disarankan:
1. Vendor mendaftar dan mengunggah identitas/SKCK.
2. Admin memeriksa kelengkapan dan persyaratan.
3. Jika dokumen kurang/salah, Admin mengembalikan pendaftaran untuk perbaikan dengan alasan yang jelas.
4. Jika persyaratan wajib tidak terpenuhi atau ditemukan masalah serius, Admin dapat menolak pendaftaran.
5. Setelah identitas dan SKCK disetujui, lokasi pertama diverifikasi.
6. Lokasi tambahan diverifikasi Admin wilayah masing-masing.
7. Vendor hanya bisa menerima booking setelah verifikasi vendor, minimal satu lokasi yang aktif, dan onboarding Xendit yang diperlukan selesai.

### 6.2 Kebijakan masa berlaku SKCK

- Pengingat dikirim 30, 14, dan 7 hari sebelum SKCK kedaluwarsa.
- Pengingat juga dikirim pada tanggal kedaluwarsa.
- Setelah kedaluwarsa, vendor mendapat masa tenggang 30 hari dan masih boleh menerima booking selama masa tenggang.
- Pengingat terakhir dikirim 7 hari sebelum masa tenggang berakhir.
- Jika masa tenggang berakhir tanpa SKCK terbaru yang telah diverifikasi, vendor tidak dapat menerima booking baru.
- Booking yang sudah berjalan tetap dilanjutkan sampai selesai hanya karena SKCK kedaluwarsa; mekanisme risiko tinggi tetap dapat mengesampingkan aturan ini jika ada isu keselamatan atau dugaan pelanggaran serius.

### 6.3 Akses dokumen SKCK

- Admin biasa hanya melihat status verifikasi dan informasi relevan.
- Admin mengajukan akses dokumen lengkap kepada Superadmin bila ada kebutuhan khusus.
- Superadmin menyetujui/menolak akses; setiap akses harus tercatat.
- Dokumen harus disimpan dengan akses terbatas, URL sementara bila dibutuhkan, enkripsi sesuai kemampuan platform, dan retention policy yang perlu ditetapkan.

## 7. Listing, lokasi, dan pembatasan vendor

### 7.1 Pengelolaan listing

- Vendor mengelola sendiri profil, layanan, harga, kapasitas, alamat, fasilitas, dan jam operasional.
- Admin dapat meminta perbaikan atau menonaktifkan lokasi untuk pelanggaran operasional biasa.
- Admin dapat mengoreksi data administratif berdasarkan bukti dan wajib memberi alasan. Harga, kapasitas, deskripsi layanan, dan informasi substantif tetap diubah oleh vendor.
- Setiap perubahan penting dicatat.

### 7.2 Kasus risiko tinggi

- Sistem memiliki kategori risiko standar, misalnya dugaan penipuan, barang hilang, ancaman keselamatan, dan dugaan tindak kriminal serius.
- Admin juga dapat mengajukan kasus di luar kategori standar dengan alasan dan bukti.
- Untuk kasus berisiko tinggi, sistem membatasi penerimaan booking baru sementara dan mengeskalasi kepada Superadmin.
- Superadmin memutuskan apakah pembatasan dipertahankan, diubah, atau dicabut.
- Pembatasan lokasi tidak otomatis memengaruhi lokasi vendor lainnya, kecuali risiko berpotensi memengaruhi seluruh akun (misalnya masalah identitas atau dugaan penipuan lintas lokasi).
- Kasus berisiko tinggi pada level akun dapat membatasi booking baru dari seluruh lokasi sementara menunggu keputusan Superadmin.
- Pembatasan booking baru tidak otomatis membatalkan booking aktif. Booking aktif ditangani menurut tingkat risiko; kasus serius memerlukan koordinasi Admin dan Superadmin.

### 7.3 Aktivasi kembali lokasi

- Lokasi yang dinonaktifkan karena pelanggaran operasional biasa dapat diaktifkan kembali oleh Admin setelah masalah diperbaiki.
- Lokasi yang dibatasi karena risiko tinggi hanya dapat dibuka kembali oleh Superadmin.
- Alasan, bukti, dan keputusan harus tercatat dalam sistem.

## 8. Siklus booking dan layanan

### 8.1 Alur utama

1. Customer mencari lokasi dan membuat booking.
2. Belum ada pembayaran pada tahap booking.
3. Vendor melihat nama customer dan detail layanan minimum, tanpa nomor telepon.
4. Vendor menerima atau menolak booking.
5. Setelah booking diterima, nomor telepon customer dan vendor tersedia bagi kedua pihak untuk koordinasi.
6. Vendor menerima barang, mengonfirmasi penerimaan, lalu memulai layanan penitipan.
7. Customer dapat meminta pengakhiran layanan kapan saja, termasuk lebih awal.
8. Saat customer ingin mengambil barang, sistem menghitung biaya akhir berdasarkan durasi aktual dan snapshot tarif.
9. Customer membayar melalui QRIS/Xendit.
10. Barang dikembalikan. Vendor menandai pengembalian barang.
11. Customer mengonfirmasi penerimaan barang; jika ada masalah, customer dapat mengajukan komplain.
12. Booking selesai dan payout diproses menurut aturan settlement final yang harus dikonfirmasi pada Open Decisions.

### 8.2 Konfirmasi pengembalian barang

- Setelah vendor menandai barang dikembalikan, customer memiliki 48 jam untuk mengonfirmasi atau mengajukan komplain.
- Sistem mengirim pengingat selama jendela 48 jam; jadwal rinci pengingat belum ditetapkan.
- Jika customer mengonfirmasi, booking dapat diselesaikan.
- Jika customer tidak mengonfirmasi dan tidak ada komplain setelah 48 jam, sistem menyelesaikan booking otomatis.
- Jika komplain tercatat, booking menjadi sengketa dan payout ditahan selama kasus tersebut memerlukan penyelesaian.

### 8.3 Pembatalan sebelum penitipan

- Customer dapat membatalkan sebelum barang diserahkan.
- Vendor dapat membatalkan booking yang telah diterima sebelum barang diterima, dengan alasan wajib.
- Admin menangani kasus khusus atau konflik.
- Karena tidak ada pembayaran saat booking, pembatalan sebelum penitipan tidak memerlukan proses refund pembayaran booking.

### 8.4 Pengakhiran lebih awal oleh customer

- Customer dapat mengakhiri layanan kapan saja tanpa persetujuan vendor.
- Biaya menggunakan durasi aktual dan aturan tarif yang disimpan pada booking.
- Vendor tidak boleh menolak pengakhiran layanan hanya karena customer mengambil barang lebih awal.
- Jika perubahan detail booking yang diminta customer ditolak pihak lain, kesepakatan awal tetap berlaku, tetapi customer tetap boleh mengakhiri layanan lebih awal sesuai aturan ini.

### 8.5 Pengakhiran lebih awal oleh vendor

- Untuk kendala biasa, vendor mengajukan pengakhiran dan customer diberi kesempatan menyepakati solusi.
- Untuk keadaan darurat atau risiko keselamatan, vendor segera melapor kepada Admin.
- Admin mengoordinasikan solusi dan pengembalian barang.
- Jika penyebab berasal dari vendor, customer membayar berdasarkan durasi aktual dan dapat mengajukan kompensasi.
- Jika terjadi keadaan darurat atau faktor di luar kendali vendor, kasus ditangani berdasarkan bukti; vendor tidak otomatis dianggap bersalah.

## 9. Perubahan booking dan perhitungan biaya

- Sebelum barang dititipkan, Admin dapat memperbarui detail operasional atas permintaan customer/vendor sesuai kewenangan.
- Setelah layanan dimulai, perubahan yang memengaruhi kesepakatan perlu disetujui kedua pihak.
- Jika perubahan tidak disetujui, detail booking awal tetap berlaku; customer tetap dapat mengakhiri layanan lebih awal.
- Admin tidak boleh mengganti nominal tagihan secara manual.
- Jika ada dugaan kesalahan perhitungan, sistem mencoba menghitung ulang dari snapshot tarif dan timestamp yang valid. Jika tidak terselesaikan otomatis, kasus dieskalasi kepada Superadmin.

### 9.1 Model tarif

- Superadmin mengatur model tarif yang tersedia per kategori layanan.
- Vendor memilih model tarif yang diizinkan untuk kategori tersebut dan menentukan nominal harganya sendiri.
- Tidak ada batas minimum/maksimum harga platform pada kebijakan saat ini.
- Model tarif, nominal harga, satuan, aturan pembulatan, serta kebijakan perhitungan disimpan sebagai snapshot pada booking.
- Perubahan konfigurasi atau harga berlaku hanya untuk booking baru.
- Tarif yang tersedia per kategori belum dirinci; perlu dikonfigurasi sebagai bagian produk.

## 10. Pembayaran QRIS, komisi, dan payout

### 10.1 Prinsip pembayaran

- Tidak ada pembayaran saat booking.
- Pembayaran dilakukan saat customer hendak mengambil barang setelah layanan penitipan.
- Metode MVP adalah QRIS melalui Xendit.
- Customer melihat biaya penitipan, kompensasi bila ada, biaya gateway, dan total pembayaran secara terpisah.
- Vendor tidak boleh menyerahkan barang sebelum pembayaran terkonfirmasi berhasil oleh provider, kecuali ada kebijakan pengecualian baru yang disetujui; saat ini tidak ada pengecualian yang ditetapkan.
- Webhook dan/atau pemeriksaan status provider menjadi sumber kebenaran pembayaran.
- Idempotensi wajib untuk mencegah pembayaran ganda, pembuatan transaksi ganda yang tidak disengaja, atau pembagian dana ganda.

### 10.2 QRIS gagal/kedaluwarsa

- Sistem dapat membuat ulang QRIS otomatis selama belum melewati 3 percobaan dalam siklus yang sama.
- Setelah 3 percobaan gagal/kedaluwarsa, sistem menghentikan pembuatan otomatis.
- Admin wilayah dapat membuka kembali proses pembayaran setelah masa tunggu 30 menit.
- Setiap pembukaan kembali memberikan siklus baru dengan maksimal 3 percobaan.
- Admin wajib memberi alasan; setiap siklus dan percobaan tercatat.
- Jumlah maksimum pembukaan kembali lintas siklus belum ditetapkan.
- Jika status transaksi belum pasti, sistem menunggu webhook dan memeriksa status API. Jangan membuat QRIS baru untuk transaksi lama yang statusnya belum pasti.
- Jika status masih belum pasti setelah 30 menit, sistem membuat kasus kepada Admin.
- Admin tidak dapat menetapkan pembayaran berhasil secara manual.

### 10.3 Komisi dan pembagian dana

Keputusan bisnis saat ini:
- Komisi TitipSini: 20% dari biaya penitipan awal sesuai snapshot booking.
- Bagian vendor: target 80% dari biaya penitipan sebelum memperhitungkan dampak kompensasi dan biaya provider.
- Biaya payment gateway ditanggung customer dan ditampilkan terpisah.
- Target pembagian dana menggunakan Xendit marketplace/split settlement ke akun platform dan sub-akun vendor; detail produk, ketersediaan fitur, dan alur settlement harus diverifikasi langsung dengan Xendit.
- Superadmin bertanggung jawab atas rekonsiliasi dan payout.

**Penting:** komisi dihitung dari biaya penitipan awal sebelum kompensasi. Jika kompensasi mengurangi tagihan customer, beban kompensasi mengurangi bagian vendor dalam model perhitungan yang dimaksud. Formula final untuk kasus ketika tagihan customer setelah kompensasi lebih kecil daripada komisi platform belum diputuskan dan harus diklarifikasi sebelum implementasi split payment.

### 10.4 Status pembayaran dan payout dipisahkan

Implementasi perlu memisahkan sekurangnya:
- Status booking/layanan.
- Status payment intent/QRIS.
- Status pembayaran aktual dari Xendit.
- Status komisi platform.
- Status settlement/payout vendor.
- Status sengketa/hold.

Pembayaran berhasil tidak berarti payout sudah berhasil. Jika pembayaran berhasil tetapi split/payout gagal, sistem melakukan retry dengan idempotensi dan eskalasi kepada Superadmin setelah batas retry yang ditetapkan di konfigurasi teknis. Pembayaran customer tidak boleh ditandai gagal hanya karena payout gagal.

## 11. Sengketa dan kompensasi

### 11.1 Sengketa biasa dan kasus wajib eskalasi

- Admin adalah penanggung jawab utama sengketa operasional biasa.
- Kategori yang wajib dieskalasikan kepada Superadmin: dugaan penipuan, indikasi manipulasi pembayaran, barang hilang, ancaman keselamatan, atau dugaan tindak kriminal serius.
- Superadmin dapat mengelola daftar kategori wajib eskalasi melalui dashboard.
- Perubahan daftar berlaku bagi kasus baru dan kasus yang masih terbuka; kasus yang sudah selesai tidak dibuka kembali otomatis.
- Payout yang belum diproses ditahan ketika ada sengketa yang relevan.
- Sengketa yang dilaporkan setelah payout diproses ditangani melalui proses terpisah.

### 11.2 Kewenangan dalam sengketa

- Admin memeriksa bukti dari customer/vendor dan menyelesaikan sengketa operasional biasa.
- Admin tidak boleh mengubah nominal tagihan secara manual.
- Jika perhitungan biaya salah, sistem menghitung ulang secara otomatis; jika gagal, eskalasi kepada Superadmin.
- Kasus finansial, penipuan, manipulasi pembayaran, kehilangan barang, ancaman keselamatan, dan dugaan tindak kriminal serius ditangani oleh Superadmin.
- Sengketa biasa yang diselesaikan Admin dapat melanjutkan payout otomatis jika pembayaran valid dan tidak ada hold lain.

### 11.3 Kompensasi

- Superadmin menentukan formula kompensasi standar per jenis kejadian.
- Admin memeriksa bukti dan menentukan hasil operasional dalam kewenangannya; kasus khusus diajukan kepada Superadmin untuk keputusan finansial.
- Kompensasi diterapkan sebagai potongan langsung pada tagihan customer.
- Nilai kompensasi tidak boleh membuat tagihan customer di bawah Rp0.
- Jika kompensasi sama dengan atau lebih besar daripada tagihan, customer membayar Rp0; surplus kompensasi tidak diberikan sebagai uang tunai atau saldo kredit.
- Komisi TitipSini tetap dihitung dari biaya penitipan awal sebelum kompensasi (pilihan yang telah ditetapkan), bukan dari tagihan setelah kompensasi.
- Bagaimana split dana ditangani jika tagihan akhir tidak cukup untuk membiayai komisi yang dihitung dari harga awal masih perlu diputuskan.

## 12. Rekening vendor dan onboarding Xendit

- Vendor harus menyelesaikan onboarding Xendit yang diperlukan sebelum dapat menerima booking.
- Perubahan rekening diajukan vendor melalui sistem.
- Verifikasi rekening baru hanya menggunakan fasilitas verifikasi/onboarding Xendit yang tersedia; jika tidak tersedia atau tidak berhasil, permintaan tertunda dan rekening baru tidak dapat digunakan. Tidak ada fallback verifikasi manual.
- Admin wilayah memeriksa hasil verifikasi Xendit dan menyetujui perubahan.
- Payout yang sudah terjadwal/siap diproses saat pengajuan perubahan rekening tetap menggunakan rekening lama yang sudah diverifikasi.
- Payout baru setelah pengajuan perubahan ditahan sampai rekening baru disetujui.
- Jika perubahan ditolak/dibatalkan, rekening lama yang masih valid tetap digunakan.
- Ketersediaan fitur verifikasi rekening dan model marketplace/vendor pada produk Xendit yang dipilih adalah prasyarat validasi sebelum produksi.

## 13. Akses data dan privasi

### 13.1 Kontak customer-vendor

- Sebelum vendor menerima booking, vendor dapat melihat nama customer dan detail layanan minimum, tetapi tidak melihat nomor telepon customer.
- Setelah booking diterima, customer dan vendor dapat melihat nomor telepon satu sama lain untuk koordinasi.
- Nomor telepon tidak ditampilkan di listing publik.
- Setelah booking selesai atau dibatalkan, kontak disembunyikan dari tampilan detail booking.
- Admin hanya mengakses data customer yang terkait dengan booking di wilayahnya dan sesuai kebutuhan operasional.

### 13.2 Audit log

- Audit log internal hanya dapat diakses Superadmin.
- Sistem tetap mencatat aktivitas penting meskipun Admin tidak dapat melihatnya.
- Audit log minimal mencatat actor, role, wilayah, entity, action, timestamp, alasan, status sebelum/sesudah, serta correlation ID untuk operasi terkait pembayaran.
- Audit log tidak boleh dapat diedit atau dihapus melalui UI operasional.

### 13.3 Dokumen sensitif

- SKCK dan dokumen identitas hanya dapat diakses sesuai kebutuhan.
- Akses dokumen lengkap memerlukan persetujuan Superadmin dan dicatat.
- Masa simpan, penghapusan, dan penanganan permintaan akses data perlu ditetapkan dalam kebijakan privasi dan retention policy terpisah sebelum produksi.

## 14. Snapshot kebijakan dan harga

Setiap booking harus menyimpan snapshot setidaknya untuk:
- Model tarif dan nominal harga.
- Satuan dan aturan pembulatan durasi.
- Nilai komisi yang berlaku.
- Rumus biaya gateway atau parameter perhitungan jika relevan.
- Kebijakan pembatalan dan kompensasi yang relevan.
- Waktu pembuatan booking dan versi kebijakan.

Perubahan kebijakan global oleh Superadmin berlaku untuk booking baru. Booking lama tetap mengikuti snapshot saat booking dibuat, kecuali ada proses koreksi resmi untuk kesalahan data yang tercatat dan disetujui.

## 15. Status yang disarankan

Status berikut adalah rancangan awal; engineer perlu memetakan ke enum/state machine yang konsisten.

### 15.1 Booking/lifecycle
- `pending_vendor_confirmation`
- `confirmed`
- `cancelled_before_dropoff`
- `awaiting_dropoff`
- `in_storage`
- `pickup_requested`
- `awaiting_payment`
- `payment_processing`
- `paid`
- `ready_for_return`
- `returned_by_vendor`
- `awaiting_customer_confirmation`
- `disputed`
- `completed`

Status perlu dipisahkan jika pembayaran dapat berlangsung bersamaan dengan status serah-terima tertentu. Jangan mengandalkan satu kolom status untuk mewakili booking, payment, payout, dan dispute sekaligus.

### 15.2 Payment
- `not_started`
- `pending`
- `processing_or_unknown`
- `succeeded`
- `failed`
- `expired`
- `cancelled`

### 15.3 Payout/settlement
- `not_eligible`
- `eligible`
- `on_hold_dispute`
- `pending_provider`
- `processing`
- `succeeded`
- `failed_retryable`
- `failed_escalated`

### 15.4 Vendor/location
Vendor account, onboarding Xendit, SKCK, dan status lokasi harus menjadi status yang berbeda. Misalnya, vendor dapat aktif secara identitas tetapi lokasi tertentu belum disetujui atau onboarding pembayaran belum selesai.

## 16. Acceptance criteria penting

### Access control
- Admin dari wilayah A tidak dapat membaca atau mengubah lokasi, vendor, booking, atau customer milik wilayah B melalui UI maupun API.
- Vendor tidak dapat mengakses atau mengubah lokasi milik vendor lain dengan memanipulasi ID.
- Customer hanya dapat mengakses booking miliknya sendiri.
- Hanya Superadmin yang dapat melihat audit log internal.
- Hanya Superadmin yang dapat mengubah kebijakan global.
- Saat Admin nonaktif, wilayah masuk ke mode pengawasan Superadmin dan setiap tindakan takeover diberi penanda.

### Vendor onboarding
- Vendor tidak dapat menerima booking sebelum identitas/SKCK, lokasi pertama, dan onboarding Xendit yang diperlukan selesai.
- Lokasi baru harus disetujui Admin wilayah lokasi tersebut.
- SKCK kedaluwarsa memulai masa tenggang 30 hari; setelah masa tenggang, booking baru diblokir sampai dokumen diperbarui dan diverifikasi.
- Booking aktif tidak otomatis dibatalkan hanya karena SKCK kedaluwarsa.

### Booking/payment
- Tidak ada transaksi pembayaran saat booking dibuat.
- Hanya QRIS yang tersedia pada MVP.
- Vendor tidak dapat menyerahkan barang sebelum pembayaran dikonfirmasi provider.
- Status payment tidak dapat diubah manual oleh Admin/Vendor.
- Retry QRIS dan reopen cycle mematuhi batas percobaan dan masa tunggu yang ditetapkan.
- Transaksi yang statusnya belum pasti tidak menghasilkan QRIS baru sampai status diselesaikan.
- Idempotency mencegah double charge dan double split/payout.
- Fee gateway ditampilkan terpisah dan ditagihkan ke customer sesuai kemampuan serta aturan Xendit.

### Dispute/compensation
- Payout ditahan selama sengketa yang relevan belum selesai.
- Kasus wajib eskalasi mengikuti konfigurasi Superadmin.
- Kompensasi tidak membuat tagihan customer negatif.
- Admin tidak dapat mengubah nominal tagihan manual.
- Semua keputusan sengketa dan kompensasi memiliki alasan dan bukti.

## 17. Open Decisions — wajib diselesaikan sebelum produksi

Informasi untuk membuat baseline sudah cukup, tetapi keputusan berikut belum cukup jelas untuk implementasi tanpa risiko salah tafsir.

1. **Komisi saat kompensasi besar:** komisi 20% dihitung dari biaya awal, sedangkan tagihan customer bisa turun setelah kompensasi. Jika tagihan akhir kurang dari komisi, bagaimana pembagian dana dilakukan? Apakah vendor menanggung seluruh selisih, TitipSini menyerapnya, atau ada batas kompensasi?
2. **Trigger payout vs jendela konfirmasi 48 jam:** payout disepakati diproses langsung setelah pembayaran berhasil dan pengembalian dikonfirmasi, sementara customer memiliki 48 jam untuk komplain dan sistem menyelesaikan otomatis setelah 48 jam jika tidak ada respons. Perlu diputuskan apakah payout menunggu konfirmasi customer/berakhirnya 48 jam, atau dapat diproses lebih awal setelah vendor menandai pengembalian.
3. **Batas pembukaan ulang QRIS:** Admin dapat membuka siklus baru setelah masa tunggu 30 menit, dengan maksimal 3 percobaan per siklus. Jumlah maksimum siklus reopen tidak ditetapkan.
4. **Kebijakan masa aktif QRIS:** durasi kedaluwarsa QRIS dan jadwal polling status mengikuti dokumentasi/konfigurasi Xendit dan harus dipilih saat implementasi.
5. **Kompensasi dan fee gateway:** apakah fee gateway tetap dibayar customer ketika kompensasi menurunkan biaya penitipan menjadi Rp0? Tidak boleh diasumsikan provider dapat menerapkan setiap konfigurasi secara persis tanpa konfirmasi.
6. **Formula detail per model tarif:** model tarif yang disediakan untuk tiap kategori layanan dan aturan pembulatan durasi belum ditetapkan.
7. **Kebijakan pembatalan setelah layanan dimulai:** customer boleh mengakhiri lebih awal, tetapi kondisi pembatalan administratif, barang belum siap diambil, atau customer tidak datang perlu dirinci.
8. **Daftar reminder 48 jam:** frekuensi/waktu notifikasi selama jendela konfirmasi belum ditentukan. WhatsApp tidak digunakan; kanal notifikasi web/email/push perlu diputuskan.
9. **Wilayah administratif tumpang tindih:** sistem perlu aturan saat satu kecamatan atau area administratif dimasukkan ke dua wilayah operasional.
10. **Retensi SKCK dan data pribadi:** jangka waktu penyimpanan, penghapusan, serta proses pembaruan/penghapusan dokumen perlu ditetapkan dengan mempertimbangkan perlindungan data.
11. **Settlement Xendit:** verifikasi produk Xendit yang mendukung onboarding vendor, QRIS, split settlement, payout langsung ke rekening vendor, fee payer, komisi, dan penanganan refund/kompensasi harus selesai sebelum status ADR pembayaran menjadi `ACCEPTED`.
12. **Kompensasi setelah payout:** sengketa yang muncul setelah payout tidak bisa menahan payout yang sudah selesai; mekanisme pemulihan atau tanggung jawab finansial perlu ditentukan.
13. **Definisi vendor berisiko tinggi:** kategori standar sudah disepakati secara konseptual, tetapi definisi operasional, bukti minimum, dan durasi pembatasan perlu dirinci.
14. **Data rekening bank:** masking, masa simpan, dan retensi riwayat rekening lama perlu didefinisikan.

## 18. Rekomendasi implementasi

1. Ubah aturan di atas menjadi permission granular, policy Laravel, middleware, dan query scoping berbasis wilayah/lokasi.
2. Pisahkan model `booking`, `payment`, `settlement/payout`, `dispute`, `vendor`, `vendor_location`, `vendor_document`, `policy_version`, dan `audit_log`.
3. Terapkan state machine untuk status booking dan status payment secara terpisah.
4. Simpan snapshot kebijakan/tarif saat booking dibuat.
5. Terapkan webhook Xendit yang terverifikasi, idempotency key, retry yang aman, dan rekonsiliasi.
6. Gunakan authorization test untuk setiap role serta negative tests terhadap akses lintas wilayah.
7. Jangan mengimplementasikan asumsi pada Open Decisions sebagai kebijakan final. Tandai sebagai feature flag atau tahan implementasinya sampai keputusan tersedia.

---

## 19. Catatan penggunaan oleh AI coding agent

- Baca file ini bersama PRD, Implementation Plan, dan ADR pembayaran yang tersedia.
- Jika ada konflik antardokumen, prioritaskan keputusan terbaru yang secara eksplisit disetujui user dan tandai konflik lainnya untuk klarifikasi.
- Jangan mengarang provider capability, hukum, biaya, atau perilaku settlement yang belum diverifikasi.
- Jangan membuat UI sebagai satu-satunya lapisan keamanan; semua akses harus ditegakkan di backend.
- Jangan mengizinkan Admin mengubah status pembayaran atau nominal finansial secara manual.
- Sebelum coding fitur pembayaran/payout, selesaikan Open Decisions 1, 2, 5, dan 11.
