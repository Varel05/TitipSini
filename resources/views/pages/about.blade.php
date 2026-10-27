@extends('layouts.app')

@section('title', 'Tentang Kami — TitipSini')

@section('content')
    <section class="py-16 bg-slate-900 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Mengenal TitipSini</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Platform Jaringan Penitipan Barang Terintegrasi</h1>
            <p class="text-sm text-slate-300">Memberdayakan pelaku UMKM lokal dan memberikan ketenangan tanpa beban bagi para pelancong dan profesional di Indonesia.</p>
        </div>
    </section>

    <section class="py-16 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white p-6 sm:p-10 rounded-3xl border border-slate-200/90 shadow-sm space-y-6 text-slate-600 text-sm leading-relaxed">
                <h2 class="text-xl font-bold text-slate-900">Misi Kami</h2>
                <p>
                    TitipSini lahir dari kebutuhan nyata para wisatawan, komuter, dan profesional yang sering kali terbebani oleh koper, tas besar, atau dokumen penting saat transit di stasiun, bandara, atau beraktivitas di pusat kota.
                </p>
                <p>
                    Melalui digitalisasi terpadu, TitipSini menghubungkan penyimpan barang dengan jaringan mitra lokal terpercaya (toko, loker stasiun, kantor perwakilan UMKM, dan safe deposit hub) yang memiliki ruang aman dan terpantau CCTV.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-slate-100 text-center">
                    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                        <div class="text-2xl font-black text-indigo-600">100%</div>
                        <div class="text-xs font-bold text-slate-800 mt-1">Verifikasi KYC Mitra</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Pemeriksaan izin dan tempat fisik</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100">
                        <div class="text-2xl font-black text-blue-600">0 Rupiah</div>
                        <div class="text-xs font-bold text-slate-800 mt-1">Biaya Pemesanan Awal</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Bayar fleksibel saat barang diambil</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-100">
                        <div class="text-2xl font-black text-teal-600">10 Juta</div>
                        <div class="text-xs font-bold text-slate-800 mt-1">Jaminan Asuransi</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Kompensasi resmi untuk keamanan</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
