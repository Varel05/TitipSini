@extends('layouts.vendor')

@section('title', 'Lembar Kerja Penitipan - ' . $booking->booking_code)
@section('page_title', 'Lembar Kerja Operasional: ' . $booking->booking_code)

@section('content')
<div class="max-w-4xl space-y-6">

    <!-- Back Navigation -->
    <div>
        <a href="{{ route('vendor.bookings.index') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-indigo-600 transition">
            <i class="bi bi-arrow-left mr-1.5"></i> Kembali ke Antrian Operasional
        </a>
    </div>

    <!-- Main Operational Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 sm:p-8 bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-bold text-indigo-400 tracking-wider">Tiket Reservasi Pelanggan</span>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-2xl sm:text-3xl font-mono font-extrabold text-white">{{ $booking->booking_code }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $booking->status->badgeClasses() }}">
                        {{ $booking->status->label() }}
                    </span>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-xs text-slate-400 block">Porsi Mitra Vendor (80% Net)</span>
                <span class="text-2xl font-black text-emerald-400">Rp {{ number_format($booking->vendor_earnings, 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-400 block">Total Tagihan: Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-8">
            
            <!-- Customer & Item Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm">
                <!-- Customer Info -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center gap-1.5">
                        <i class="bi bi-person-circle text-indigo-600"></i> Data Pelanggan
                    </h3>
                    <div class="space-y-2 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nama:</span>
                            <span class="font-bold text-slate-900">{{ $booking->customer->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nomor Telepon:</span>
                            <span class="font-bold text-slate-900 font-mono">{{ $booking->customer->phone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Email:</span>
                            <span class="font-medium text-slate-800">{{ $booking->customer->email }}</span>
                        </div>
                        @if($booking->customer_notes)
                            <div class="pt-2 border-t border-slate-200 text-xs">
                                <span class="text-slate-400 block mb-1">Catatan Pelanggan:</span>
                                <span class="text-slate-800 italic">"{{ $booking->customer_notes }}"</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Storage Info -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center gap-1.5">
                        <i class="bi bi-box-seam text-indigo-600"></i> Rincian Penitipan
                    </h3>
                    <div class="space-y-2 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Lokasi / Loker:</span>
                            <span class="font-bold text-slate-900">{{ $booking->listing->title }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Rencana Drop:</span>
                            <span class="font-bold text-slate-900">{{ $booking->check_in_plan_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Durasi Disepakati:</span>
                            <span class="font-bold text-slate-900">{{ $booking->duration_units }} {{ $booking->duration_type === 'day' ? 'Hari' : 'Jam' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Total Unit Barang:</span>
                            <span class="font-bold text-indigo-600">{{ $booking->total_items }} Unit</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="space-y-3">
                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-500">Daftar Barang Fisik</h4>
                <div class="bg-slate-50 rounded-2xl p-4 divide-y divide-slate-200/80">
                    @foreach($booking->items as $item)
                        <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between text-xs sm:text-sm">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-tag-fill text-indigo-600"></i>
                                <span class="font-bold text-slate-800">{{ $item->item_name }}</span>
                            </div>
                            <span class="text-xs font-mono uppercase bg-white border border-slate-200 px-2.5 py-1 rounded-lg">
                                Ukuran: {{ $item->item_size }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- MODULE 1: Physical Check-In & Security Sealing -->
            <div class="p-6 rounded-3xl bg-indigo-50/50 border border-indigo-100 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-indigo-600"></i> Segel Pengaman Fisik &amp; Check-In
                        </h3>
                        <p class="text-xs text-slate-500">Setiap barang wajib dipasangi segel barcode resmi saat diterima dari pelanggan</p>
                    </div>

                    @if($booking->security_seal_code)
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                            <i class="bi bi-check-circle-fill text-emerald-600"></i> {{ $booking->security_seal_code }}
                        </span>
                    @endif
                </div>

                @if($booking->status->value === 'confirmed')
                    <form action="{{ route('vendor.bookings.checkin', $booking->booking_code) }}" method="POST" class="space-y-3 pt-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="security_seal_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Nomor Segel Fisik (Barcode)
                                </label>
                                <input id="security_seal_code" name="security_seal_code" type="text" required
                                    value="SEAL-{{ strtoupper(Str::random(6)) }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-mono font-bold"
                                    placeholder="SEAL-XXXXXX">
                            </div>
                            <div>
                                <label for="check_in_notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Catatan Kondisi Barang (Opsional)
                                </label>
                                <input id="check_in_notes" name="check_in_notes" type="text"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                                    placeholder="Contoh: Koper mulus, resleting utuh">
                            </div>
                        </div>

                        <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                            <i class="bi bi-qr-code-scan"></i> Konfirmasi Terima Barang &amp; Kunci Segel
                        </button>
                    </form>
                @elseif($booking->isCheckedIn() || $booking->isPendingPayment() || $booking->isCompleted())
                    <div class="p-3 bg-white rounded-xl border border-indigo-200 text-xs text-indigo-900 flex items-center justify-between">
                        <span>Check-in fisik tercatat pada: <strong>{{ $booking->actual_check_in_at?->translatedFormat('d M Y, H:i') ?? 'Tersimpan' }} WIB</strong></span>
                        <span class="font-mono text-emerald-700 font-bold"><i class="bi bi-lock-fill"></i> Tersegel Aman</span>
                    </div>
                @endif
            </div>

            <!-- MODULE 2: Handover & Payment Verification (ADR-002) -->
            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200 space-y-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="bi bi-cash-coin text-emerald-600"></i> Status Pembayaran &amp; Serah Terima Barang
                    </h3>
                    <p class="text-xs text-slate-500">Sesuai tata kelola ADR-002, barang HANYA boleh diserahkan jika pembayaran Xendit berstatus LUNAS</p>
                </div>

                @php
                    $latestPayment = $booking->paymentAttempts->last();
                    $isPaid = $latestPayment && $latestPayment->status->value === 'paid';
                @endphp

                <!-- Status Banner -->
                <div class="p-4 rounded-2xl border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 {{ $isPaid ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' }}">
                    <div class="flex items-center gap-2 text-xs sm:text-sm">
                        <i class="bi {{ $isPaid ? 'bi-check-circle-fill text-emerald-600' : 'bi-clock-history text-amber-600' }} text-lg"></i>
                        <div>
                            <strong>{{ $isPaid ? 'Pembayaran Xendit Telah LUNAS' : 'Menunggu Pelunasan Pembayaran oleh Pelanggan' }}</strong>
                            <span class="block text-xs {{ $isPaid ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $isPaid ? 'Metode: ' . strtoupper($latestPayment->channel ?? 'Xendit') . ' • Ref: ' . $latestPayment->payment_reference : 'Pelanggan belum menyelesaikan checkout via QRIS/VA' }}
                            </span>
                        </div>
                    </div>

                    <span class="font-black text-sm {{ $isPaid ? 'text-emerald-700' : 'text-amber-700' }}">
                        Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Handover Actions -->
                @if($booking->status->value === 'retrieval_requested')
                    <div class="pt-2">
                        @if($isPaid)
                            <form action="{{ route('vendor.bookings.handover', $booking->booking_code) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/25 transition flex items-center justify-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i> Konfirmasi Serah Terima Barang ke Pelanggan (Selesaikan Booking)
                                </button>
                            </form>
                        @else
                            <div class="space-y-3">
                                <p class="text-xs text-rose-600 font-semibold flex items-center gap-1.5">
                                    <i class="bi bi-shield-slash-fill"></i> Tombol serah-terima terkunci sampai pembayaran dikonfirmasi server.
                                </p>
                                <div class="flex items-center gap-3">
                                    <!-- Shortcut simulate pay for vendor test -->
                                    <form action="{{ route('booking.simulate_pay', $booking->booking_code) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm transition">
                                            <i class="bi bi-lightning-charge"></i> Simulasi Bayar Lunas (Demo Xendit)
                                        </button>
                                    </form>

                                    <button onclick="window.location.reload();" class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                                        <i class="bi bi-arrow-clockwise"></i> Cek Ulang Status Pembayaran
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif($booking->isCompleted())
                    <div class="p-4 rounded-2xl bg-emerald-100 text-emerald-900 text-xs font-medium flex items-center gap-2">
                        <i class="bi bi-patch-check-fill text-emerald-700 text-base"></i>
                        <span>Layanan selesai secara penuh. Barang telah diserahkan kembali pada {{ $booking->actual_check_out_at?->translatedFormat('d M Y, H:i') ?? 'hari ini' }} WIB. Payout telah tercatat.</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
