@extends('layouts.app')

@section('title', 'Syarat, Ketentuan & Kebijakan Privasi — TitipSini')

@section('content')
    <section class="py-16 bg-slate-900 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Legalitas &amp; Keamanan</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Syarat &amp; Ketentuan Layanan</h1>
            <p class="text-sm text-slate-300">Ketentuan hak dan kewajiban pengguna dan mitra penyedia tempat penyimpanan barang TitipSini.</p>
        </div>
    </section>

    <section class="py-16 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-6 sm:p-10 rounded-3xl border border-slate-200/90 shadow-sm space-y-8 text-slate-600 text-xs sm:text-sm leading-relaxed">
                <section class="space-y-3">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">1. Ketentuan Umum Penitipan</h2>
                    <p>
                        Dengan melakukan pemesanan slot penitipan di platform TitipSini, Anda menyetujui bahwa barang yang dititipkan adalah milik pribadi yang sah dan tidak melanggar ketentuan hukum Republik Indonesia.
                    </p>
                    <p>
                        Pemesanan tidak dikenakan biaya di awal (Zero Upfront Fee). Masa penitipan dihitung berdasarkan durasi aktual sejak serah terima barang (Check-in) hingga barang diambil kembali (Check-out).
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">2. Prosedur Serah Terima &amp; Barcode Segel</h2>
                    <p>
                        Setiap tas, koper, atau wadah penyimpanan wajib dilakukan penempelan segel barcode (tamper-evident seal) oleh petugas mitra di hadapan penyimpan. Pengambilan barang hanya dapat dilakukan oleh penyimpan sah yang dapat menunjukkan kode booking digital dan bukti verifikasi identitas yang cocok.
                    </p>
                </section>

                <section id="kebijakan-privasi" class="space-y-3 pt-6 border-t border-slate-100">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">3. Kebijakan Privasi Data</h2>
                    <p>
                        Kami menjaga kerahasiaan data pribadi pelanggan, termasuk nomor telepon, alamat email, dan dokumen identitas pendukung. Seluruh data identitas disimpan dalam storage privat terenkripsi dan hanya diakses oleh petugas admin resmi untuk keperluan verifikasi kepatuhan hukum dan klaim asuransi.
                    </p>
                </section>
            </div>
        </div>
    </section>
@endsection
