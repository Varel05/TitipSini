@extends('layouts.app')

@section('title', 'Sesi Pembayaran Xendit - ' . $booking->booking_code)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Back to Booking -->
    <div class="mb-6">
        <a href="{{ route('booking.show', $booking->booking_code) }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-emerald-600 transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Tiket Penitipan
        </a>
    </div>

    <!-- Payment Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl"></div>
            
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-sm">
                        TS
                    </span>
                    <span class="font-bold text-lg tracking-tight">Titip<span class="text-emerald-400">Sini</span> Pay</span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    xenPlatform Gateway
                </div>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1">Tagihan Penutupan Layanan (Zero Upfront Closeout)</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-extrabold text-white">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                    <span class="text-sm text-slate-400">IDR</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-slate-400">
                    <span>Kode: <strong class="font-mono text-emerald-400 font-semibold">{{ $booking->booking_code }}</strong></span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1 bg-slate-800 px-2 py-0.5 rounded text-amber-300 font-semibold">
                        <i class="bi bi-arrow-repeat"></i> Percobaan {{ $booking->getCurrentCycleAttemptsCount() }}/3 (Siklus #{{ $booking->current_payment_cycle }})
                    </span>
                    <span>•</span>
                    <span class="text-slate-300">Kedaluwarsa: 15 Menit</span>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <!-- Details Breakdown -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Rincian Pembayaran & Split Finansial</h3>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Lokasi Penitipan</span>
                        <span class="font-medium text-slate-900">{{ $booking->listing->title }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Durasi Aktual</span>
                        <span class="font-medium text-slate-900">{{ $booking->duration_units }} {{ $booking->duration_type === 'hour' ? 'Jam' : 'Hari' }} ({{ $booking->total_items }} Barang)</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Jasa Penitipan</span>
                        <span class="font-medium text-slate-900">Rp {{ number_format($booking->storage_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Payment Gateway (Xendit)</span>
                        <span class="font-medium text-emerald-600">Gratis (Ditanggung)</span>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-between font-bold text-base text-slate-900">
                        <span>Total Pembayaran</span>
                        <span class="text-emerald-600">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- xenPlatform Settlement Breakdown Note (ADR-002) -->
                <div class="mt-4 pt-3 border-t border-dashed border-slate-300 bg-white/70 -mx-2 p-3 rounded-xl text-xs text-slate-500">
                    <p class="font-semibold text-slate-700 flex items-center gap-1.5 mb-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Otomasi xenPlatform Split Settlement (ADR-002)
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="bg-slate-100 p-2 rounded-lg">
                            <span class="text-slate-500 block">Komisi Platform (20%):</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($booking->platform_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-emerald-50 p-2 rounded-lg border border-emerald-100">
                            <span class="text-emerald-700 block">Mitra Vendor (80%):</span>
                            <span class="font-bold text-emerald-800">Rp {{ number_format($booking->vendor_earnings, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Simulated Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Pilih Metode Pembayaran</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-3 p-3.5 rounded-2xl border-2 border-emerald-500 bg-emerald-50/50 cursor-pointer">
                        <input type="radio" name="payment_channel" value="qris" checked class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">QRIS Instan</span>
                            <span class="text-[10px] text-slate-500">Semua E-Wallet / Mobile Banking</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer">
                        <input type="radio" name="payment_channel" value="bca_va" class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">BCA VA</span>
                            <span class="text-[10px] text-slate-500">Virtual Account Otomatis</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer">
                        <input type="radio" name="payment_channel" value="gopay" class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">GoPay / Shopee</span>
                            <span class="text-[10px] text-slate-500">E-Wallet Apps</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Interactive Action -->
            @if($booking->isPaymentAttemptLimitExceeded())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-2">
                    <strong class="font-bold flex items-center gap-1.5 text-rose-900">
                        <i class="bi bi-exclamation-triangle-fill"></i> Kuota Percobaan QRIS Telah Habis (3/3)
                    </strong>
                    <p class="leading-relaxed">
                        Anda telah mencapai batas 3 kali percobaan QRIS pada siklus ini. Pembuatan invoice ditangguhkan secara otomatis. Silakan tunggu pembukaan kembali oleh Admin Wilayah.
                    </p>
                </div>
            @else
                <form action="{{ route('booking.simulate_pay', $booking->booking_code) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base shadow-lg shadow-emerald-600/25 transition active:scale-[0.99] gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Bayar Sekarang (Konfirmasi Selesai & Payout)
                    </button>
                </form>
            @endif

            <div class="flex items-center justify-center gap-4 text-xs text-slate-400">
                <span class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                    </svg>
                    256-Bit SSL Encrypted
                </span>
                <span>•</span>
                <span>Powered by Xendit xenPlatform</span>
            </div>
        </div>
    </div>
</div>
@endsection
