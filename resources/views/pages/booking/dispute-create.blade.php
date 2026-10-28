@extends('layouts.app')

@section('title', 'Ajukan Pengaduan & Sengketa - ' . $booking->booking_code)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Back to Booking -->
    <div class="mb-6">
        <a href="{{ route('booking.show', $booking->booking_code) }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-purple-600 transition">
            <i class="bi bi-arrow-left mr-1.5"></i> Kembali ke Tiket Penitipan
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xl overflow-hidden">
        <div class="p-6 sm:p-8 bg-gradient-to-r from-rose-950 via-slate-900 to-slate-900 text-white">
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 font-bold text-sm">
                    <i class="bi bi-shield-exclamation"></i>
                </span>
                <span class="font-bold text-sm tracking-wide uppercase text-rose-300">Pusat Mediasi & Sengketa (Policy 11)</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white">Laporkan Masalah Layanan Penitipan</h1>
            <p class="text-xs text-slate-300 mt-1">
                Reservasi: <strong class="font-mono text-white">{{ $booking->booking_code }}</strong> • {{ $booking->listing->title }}
            </p>
        </div>

        <form action="{{ route('booking.dispute.store', $booking->booking_code) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Information Alert -->
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1.5 leading-relaxed">
                <strong class="font-bold flex items-center gap-1.5 text-amber-950">
                    <i class="bi bi-info-circle-fill text-amber-600"></i> Perlindungan Hak Pelanggan & Escrow Hold (Policy 11.1)
                </strong>
                <p>
                    Saat pengaduan diajukan, status pesanan otomatis beralih menjadi <strong>Sengketa (Disputed)</strong> dan pencairan dana ke rekening mitra akan <strong>ditahan sementara (Escrow Hold)</strong> sampai penyelidikan selesai.
                </p>
            </div>

            <!-- Category Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Kategori Masalah <span class="text-rose-500">*</span>
                </label>
                <div class="space-y-2">
                    @foreach($categories as $category)
                        <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200 hover:border-purple-300 bg-white hover:bg-slate-50/50 cursor-pointer transition">
                            <input type="radio" name="category" value="{{ $category->value }}" {{ $loop->first ? 'checked' : '' }}
                                class="mt-0.5 text-purple-600 focus:ring-purple-500">
                            <div class="text-xs">
                                <span class="font-bold text-slate-800 block">{{ $category->label() }}</span>
                                @if($category->requiresSuperadminEscalation())
                                    <span class="text-[10px] font-semibold text-purple-700 block mt-0.5">
                                        <i class="bi bi-shield-fill-check"></i> Kasus kritis wajib eskalasi langsung ke Superadmin
                                    </span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Subject / Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Judul Ringkas Masalah <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" required minlength="5" maxlength="200"
                    placeholder="Contoh: Koper mengalami lecet pada sisi roda saat pengambilan"
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent transition">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Kronologi & Penjelasan Detail <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" rows="5" required minlength="10" maxlength="2000"
                    placeholder="Jelaskan secara kronologis peristiwa yang terjadi, kondisi fisik barang saat diserahkan vs diterima kembali, dan kesepakatan dengan mitra..."
                    class="w-full p-4 rounded-2xl border border-slate-200 text-xs outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent transition"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full py-4 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-lg shadow-rose-600/25 transition active:scale-[0.99] flex items-center justify-center gap-2">
                <i class="bi bi-send-fill"></i> Kirim Laporan Sengketa & Tahan Dana Mitra
            </button>
        </form>
    </div>

</div>
@endsection
