@extends('layouts.app')

@section('title', 'Pusat Bantuan & Cara Kerja — TitipSini')

@section('content')
    <section class="py-16 bg-slate-900 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Pertanyaan yang Sering Diajukan</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Pusat Bantuan &amp; Panduan Pengguna</h1>
            <p class="text-sm text-slate-300">Temukan jawaban lengkap seputar alur penitipan, kebijakan pembayaran, dan jaminan keamanan TitipSini.</p>
        </div>
    </section>

    <section class="py-16 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- FAQ Accordion / Cards -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-sm space-y-6">
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-question-circle-fill text-indigo-600"></i> Alur Pemesanan &amp; Pembayaran
                    </h2>

                    <div class="space-y-3 text-sm text-slate-600">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <h3 class="font-bold text-slate-900">Apakah saya harus membayar saat melakukan booking di website?</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Tidak. Sistem TitipSini menerapkan kebijakan <strong>Zero Upfront Payment</strong>. Anda melakukan pemesanan slot secara gratis. Pembayaran baru dilakukan secara digital (QRIS/Virtual Account) saat Anda datang ke lokasi mitra untuk mengambil kembali barang Anda.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <h3 class="font-bold text-slate-900">Bagaimana proses verifikasi barang saat serah terima?</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Saat Anda menyerahkan barang ke mitra, petugas akan memeriksa fisik luar, mendokumentasikan foto kondisi barang, dan memasang segel barcode sekali pakai (anti-tamper seal). Kode ini tercatat otomatis di tiket digital Anda.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <h3 class="font-bold text-slate-900">Metode pembayaran apa saja yang didukung?</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Kami bekerjasama dengan payment gateway resmi (Xendit) yang mendukung pembayaran instan QRIS (GoPay, OVO, Dana, ShopeePay, BCA Mobile), Virtual Account bank nasional (BCA, Mandiri, BNI, BRI), dan transfer perbankan.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="asuransi" class="space-y-4 pt-6 border-t border-slate-100">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-shield-fill-check text-emerald-600"></i> Jaminan Keamanan &amp; Asuransi
                    </h2>

                    <div class="space-y-3 text-sm text-slate-600">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <h3 class="font-bold text-slate-900">Bagaimana jika barang saya rusak atau hilang?</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Seluruh penitipan resmi melalui sistem TitipSini secara otomatis dilindungi asuransi jaminan kehilangan dan kerusakan hingga nominal Rp 10.000.000 (sepuluh juta rupiah) per transaksi, dengan proses klaim transparan didukung bukti foto serah terima dan rekaman CCTV mitra.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <h3 class="font-bold text-slate-900">Barang apa saja yang dilarang untuk dititipkan?</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Barang yang dilarang mencakup: senjata api/tajam, bahan kimia mudah meledak/terbakar, narkotika &amp; obat-obatan terlarang, hewan hidup, makanan basah yang cepat membusuk, serta barang hasil tindak kriminal atau yang melanggar hukum RI.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
