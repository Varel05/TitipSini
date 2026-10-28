@extends('layouts.admin')

@section('title', 'Detail Booking ' . $booking->booking_code . ' - Admin')
@section('page_title', 'Detail Reservasi #' . $booking->booking_code)

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Top Navigation / Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-purple-600 transition">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Booking
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $booking->status->badgeClasses() }}">
                Status: {{ $booking->status->label() }}
            </span>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-sm"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Grid: Info & Reopen Controls -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Booking Info & QRIS Telemetry -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card: Booking & Customer Details -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Informasi Reservasi</h3>
                        <span class="text-xs text-slate-400">Kode: <strong class="font-mono text-purple-700">{{ $booking->booking_code }}</strong></span>
                    </div>
                    <span class="text-xs text-slate-500">
                        Dibuat: {{ $booking->created_at->format('d M Y, H:i') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                    <!-- Customer Section -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                        <span class="font-bold text-slate-400 uppercase text-[10px] block">Pelanggan</span>
                        <div class="font-bold text-slate-800 text-sm">{{ $booking->customer?->name ?? 'Pelanggan Tamu' }}</div>
                        <div class="text-slate-600 font-mono">{{ $booking->customer?->email ?? '-' }}</div>
                        <div class="text-purple-700 font-mono font-semibold">
                            <i class="bi bi-telephone"></i> {{ $booking->getCustomerPhoneDisplay() }}
                        </div>
                    </div>

                    <!-- Vendor Section -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                        <span class="font-bold text-slate-400 uppercase text-[10px] block">Mitra Penyedia & Tempat</span>
                        <div class="font-bold text-slate-800 text-sm">{{ $booking->vendorProfile?->business_name ?? '-' }}</div>
                        <div class="text-slate-600">{{ $booking->listing?->title }} • {{ $booking->listing?->city }}</div>
                        <div class="text-purple-700 font-mono font-semibold">
                            <i class="bi bi-telephone"></i> {{ $booking->getVendorPhoneDisplay() }}
                        </div>
                    </div>
                </div>

                <!-- Storage Details & Fees -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-purple-50/60 border border-purple-100 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[10px]">Durasi</span>
                        <strong class="text-slate-900 font-bold">{{ $booking->duration_units }} {{ $booking->duration_type === 'hour' ? 'Jam' : 'Hari' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Jumlah Barang</span>
                        <strong class="text-slate-900 font-bold">{{ $booking->total_items }} Unit</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Segel Keamanan</span>
                        <strong class="font-mono text-purple-700 font-bold">{{ $booking->security_seal_code ?? 'Belum Check-In' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Total Tagihan</span>
                        <strong class="text-emerald-700 font-black text-sm">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Card: Payment Attempts History across Cycles -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Riwayat Percobaan QRIS Xendit (Policy 10.2)</h3>
                        <p class="text-xs text-slate-500">Mencatat seluruh siklus invoice QRIS, masa kedaluwarsa 15 menit, dan audit percobaan.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-purple-50 text-purple-700 font-bold text-xs border border-purple-200">
                        Siklus Aktif: #{{ $booking->current_payment_cycle }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                                <th class="py-3 px-4">Siklus</th>
                                <th class="py-3 px-4">Referensi Invoice</th>
                                <th class="py-3 px-4">Nominal</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Kedaluwarsa (15 Menit)</th>
                                <th class="py-3 px-4">Waktu Dibuat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                            @forelse($booking->paymentAttempts as $attempt)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 font-bold text-purple-700">
                                        #{{ $attempt->cycle_number }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-800">
                                        {{ $attempt->payment_reference }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900 font-sans">
                                        Rp {{ number_format($attempt->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 font-sans">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $attempt->status->badgeClasses() }}">
                                            {{ $attempt->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 font-sans">
                                        {{ $attempt->expires_at ? $attempt->expires_at->format('d M, H:i') : '-' }}
                                        @if($attempt->isExpired() && $attempt->status->value !== 'paid')
                                            <span class="text-rose-600 text-[10px] font-semibold">(Expired)</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 font-sans">
                                        {{ $attempt->created_at->format('d M Y, H:i:s') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 font-sans">
                                        Belum ada percobaan pembayaran QRIS yang dibuat untuk reservasi ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Column 3: Policy 10.2 Admin Reopen QRIS Module -->
        <div class="space-y-6">

            <!-- Card: Reopen Authorization Module -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">Otorisasi Reopen QRIS</h4>
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Policy 10.2 Standar</span>
                    </div>
                </div>

                <!-- Status Monitor -->
                <div class="p-4 rounded-2xl {{ $isLimitExceeded ? 'bg-rose-50 border border-rose-200' : 'bg-slate-50 border border-slate-200' }} space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-700">Percobaan Siklus Ini:</span>
                        <span class="font-black {{ $isLimitExceeded ? 'text-rose-700' : 'text-slate-900' }}">
                            {{ $booking->getCurrentCycleAttemptsCount() }} / 3 Percobaan
                        </span>
                    </div>

                    @if($isLimitExceeded)
                        <p class="text-rose-700 text-[11px] leading-relaxed">
                            <i class="bi bi-exclamation-octagon-fill"></i> <strong>Batas 3x QRIS Terlampaui.</strong> Sistem telah memblokir pembuatan invoice otomatis pelanggan.
                        </p>
                    @else
                        <p class="text-slate-600 text-[11px] leading-relaxed">
                            <i class="bi bi-check-circle-fill text-emerald-600"></i> Sistem mengizinkan pelanggan membuat QRIS hingga maksimal 3 kali.
                        </p>
                    @endif
                </div>

                @if($isLimitExceeded)
                    <!-- Cooldown / Reopen Status -->
                    @if($canReopen)
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-4">
                            <div class="flex items-start gap-2">
                                <i class="bi bi-check-circle-fill text-emerald-600 text-sm mt-0.5"></i>
                                <div>
                                    <strong class="text-emerald-900 text-xs block">Masa Tunggu 30 Menit Selesai</strong>
                                    <span class="text-[11px] text-emerald-700">Admin Wilayah kini berwenang membuka kembali siklus pembayaran baru.</span>
                                </div>
                            </div>

                            <form action="{{ route('admin.bookings.reopen_payment', $booking->booking_code) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Alasan Pembukaan Kembali (Wajib Dicatat) <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="reason" rows="3" required minlength="5" maxlength="500"
                                        placeholder="Contoh: Customer mengalami kendala koneksi saat scanning QRIS di lokasi vendor..."
                                        class="w-full p-2.5 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"></textarea>
                                </div>

                                <button type="submit" class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20 transition flex items-center justify-center gap-2">
                                    <i class="bi bi-unlock-fill"></i> Buka Siklus Pembayaran Baru (+3 Kuota)
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-2">
                            <div class="flex items-start gap-2">
                                <i class="bi bi-clock-history text-amber-600 text-sm mt-0.5"></i>
                                <div>
                                    <strong class="text-xs block">Masa Tunggu 30 Menit Berjalan</strong>
                                    <p class="text-[11px] text-amber-800 leading-relaxed mt-1">
                                        Sesuai Policy 10.2, pembukaan kembali hanya dapat dilakukan setelah masa tunggu 30 menit pasca-percobaan terakhir kedaluwarsa.
                                    </p>
                                    @php
                                        $mins = ceil($cooldownRemainingSeconds / 60);
                                    @endphp
                                    <div class="mt-2 text-xs font-mono font-bold text-amber-900 bg-amber-100/80 px-2.5 py-1 rounded inline-block">
                                        Sisa Waktu Cooldown: ~{{ $mins }} menit lagi
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Past Reopen Notes (if any) -->
                @if($booking->payment_cycle_reopened_at)
                    <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">Catatan Pembukaan Terakhir</span>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-700 space-y-1">
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span>Oleh: <strong class="text-slate-700">{{ $booking->paymentReopenedBy?->name ?? 'Admin' }}</strong></span>
                                <span>{{ $booking->payment_cycle_reopened_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-slate-800 italic text-[11px]">"{{ $booking->payment_reopen_reason }}"</p>
                        </div>
                    </div>
                @endif

                <!-- Security Notice -->
                <div class="text-[10px] text-slate-400 bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1">
                    <strong class="text-slate-600 block"><i class="bi bi-info-circle"></i> Prinsip Integritas (Policy 10.2 Poin 6)</strong>
                    <p>Admin dilarang menetapkan pembayaran Lunas secara manual. Status pembayaran mutlak ditentukan oleh webhook resmi Xendit.</p>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
