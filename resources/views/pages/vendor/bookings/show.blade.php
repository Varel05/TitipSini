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
                <!-- Customer Info with Privacy Masking (Policies 8.1 & 13.1) -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="bi bi-person-circle text-indigo-600"></i> Data Pelanggan
                        </span>
                        @if($booking->canViewContacts())
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                <i class="bi bi-telephone-fill"></i> Kontak Terbuka
                            </span>
                        @else
                            <span class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                <i class="bi bi-shield-lock-fill"></i> Privasi Kontak Aktif
                            </span>
                        @endif
                    </h3>
                    <div class="space-y-2 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nama Pelanggan:</span>
                            <span class="font-bold text-slate-900">{{ $booking->customer->name }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                            <span class="text-slate-400">Nomor WhatsApp / HP:</span>
                            @if($booking->canViewContacts())
                                <span class="font-bold text-slate-900 font-mono flex items-center gap-1">
                                    <i class="bi bi-whatsapp text-emerald-600"></i> {{ $booking->customer->phone }}
                                </span>
                            @else
                                <span class="text-[11px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                    <i class="bi bi-eye-slash-fill"></i> {{ $booking->getCustomerPhoneDisplay() }}
                                </span>
                            @endif
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
                            <span class="font-medium text-slate-800">{{ $booking->check_in_plan_at?->translatedFormat('d M Y, H:i') ?? '-' }} WIB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Rencana Ambil:</span>
                            <span class="font-medium text-slate-800">{{ $booking->check_out_plan_at?->translatedFormat('d M Y, H:i') ?? '-' }} WIB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Durasi Booking:</span>
                            <span class="font-bold text-indigo-700">{{ $booking->duration_units }} {{ $booking->duration_type === 'hour' ? 'Jam' : 'Hari' }} ({{ $booking->total_items }} Unit)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Breakdown Table -->
            <div class="space-y-3">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                    <i class="bi bi-card-checklist text-indigo-600"></i> Manifest Barang Titipan
                </h3>
                <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                            <tr>
                                <th class="py-3 px-4">Nama Barang</th>
                                <th class="py-3 px-4">Kategori &amp; Ukuran</th>
                                <th class="py-3 px-4">Estimasi Berat</th>
                                <th class="py-3 px-4">Catatan Khusus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($booking->items as $item)
                                <tr>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $item->item_name }}</td>
                                    <td class="py-3 px-4">{{ $item->category_name ?? 'Umum' }} • <span class="capitalize">{{ $item->item_size ?? 'medium' }}</span></td>
                                    <td class="py-3 px-4">{{ $item->weight_kg ? $item->weight_kg . ' kg' : '-' }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $item->notes ?? 'Tidak ada catatan' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 px-4 text-center text-slate-400">Belum ada rincian manifest barang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODULE 0: Vendor Acceptance Decision (Policy 8.1 Poin 3 & 4) -->
            @if($booking->status->value === 'created')
                <div class="p-6 rounded-3xl bg-amber-50/80 border border-amber-200 space-y-4">
                    <div class="flex items-center gap-2 text-amber-950 font-bold text-sm">
                        <i class="bi bi-clock-history text-amber-600 text-lg"></i>
                        Persetujuan Reservasi Masuk (Kebijakan 8.1 Poin 4)
                    </div>
                    <p class="text-xs text-amber-900 leading-relaxed">
                        Pelanggan telah mengajukan reservasi. Sesuai Kebijakan 13.1, nomor telepon pelanggan disembunyikan sampai Anda menyetujui pesanan ini. Setelah disetujui, kontak WhatsApp akan terbuka untuk koordinasi titik temu.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <!-- Reject Form with Reason -->
                        <form action="{{ route('vendor.bookings.reject', $booking->booking_code) }}" method="POST" class="inline w-full sm:w-auto" onsubmit="return confirm('Apakah Anda yakin ingin menolak pesanan ini?');">
                            @csrf
                            <input type="hidden" name="rejection_reason" value="Kapasitas ruang penuh atau jam operasional tidak mencukupi untuk jadwal tersebut.">
                            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white border border-rose-300 hover:bg-rose-50 text-rose-700 font-bold text-xs transition">
                                <i class="bi bi-x-circle"></i> Tolak Reservasi
                            </button>
                        </form>

                        <!-- Accept Form -->
                        <form action="{{ route('vendor.bookings.confirm', $booking->booking_code) }}" method="POST" class="inline w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Terima Booking &amp; Buka Kontak Pelanggan
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- MODULE 1: Check-in & Physical Barcode Seal Tag -->
            <div class="p-6 rounded-3xl bg-indigo-50/60 border border-indigo-200 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-indigo-950 text-sm flex items-center gap-2">
                            <i class="bi bi-upc-scan text-indigo-600 text-lg"></i>
                            Penerimaan Fisik &amp; Pemasangan Segel Barcode
                        </h3>
                        <p class="text-xs text-indigo-800">Wajib memasang tag segel fisik pada resleting koper / tali pengikat kardus saat barang diserahkan</p>
                    </div>

                    @if($booking->security_seal_code)
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-indigo-600 text-white shadow-sm">
                            <i class="bi bi-shield-lock-fill"></i> {{ $booking->security_seal_code }}
                        </span>
                    @endif
                </div>

                @if($booking->status->value === 'confirmed')
                    <!-- Check-in Form -->
                    <form action="{{ route('vendor.bookings.checkin', $booking->booking_code) }}" method="POST" class="space-y-4 pt-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Barcode Tag Segel Fisik *</label>
                                <input type="text" name="security_seal_code" placeholder="Contoh: SEAL-89234" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs uppercase font-bold text-slate-800">
                                <span class="text-[11px] text-slate-500 mt-0.5 block">Ketik nomor barcode yang tertempel pada segel fisik</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Kondisi Fisik (Opsional)</label>
                                <input type="text" name="check_in_notes" placeholder="Contoh: Koper ada goresan kecil di roda kiri"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs text-slate-800">
                            </div>
                        </div>

                        <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                            <i class="bi bi-qr-code-scan"></i> Konfirmasi Terima Barang &amp; Kunci Segel
                        </button>
                    </form>
                @elseif($booking->isCheckedIn() || $booking->isPendingPayment() || $booking->isAwaitingCustomerConfirmation() || $booking->isCompleted())
                    <div class="p-3 bg-white rounded-xl border border-indigo-200 text-xs text-indigo-900 flex items-center justify-between">
                        <span>Check-in fisik tercatat pada: <strong>{{ $booking->actual_check_in_at?->translatedFormat('d M Y, H:i') ?? 'Tersimpan' }} WIB</strong></span>
                        <span class="font-mono text-emerald-700 font-bold"><i class="bi bi-lock-fill"></i> Tersegel Aman</span>
                    </div>
                @endif
            </div>

            <!-- MODULE 2: Handover & Payment Verification (ADR-002 & Policy 8.2) -->
            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200 space-y-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="bi bi-cash-coin text-emerald-600"></i> Status Pembayaran &amp; Pengembalian Barang
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

                <!-- Handover Actions (Policy 8.2) -->
                @if($booking->status->value === 'retrieval_requested')
                    <div class="pt-2">
                        @if($isPaid)
                            <form action="{{ route('vendor.bookings.mark_returned', $booking->booking_code) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/25 transition flex items-center justify-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i> Tandai Barang Telah Dikembalikan (Buka Jendela Konfirmasi 48 Jam)
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
                @elseif($booking->status->value === 'returned_by_vendor')
                    <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-xs font-medium space-y-1">
                        <div class="flex items-center gap-2 font-bold text-sky-950">
                            <i class="bi bi-hourglass-split text-sky-600 text-base"></i>
                            Menunggu Konfirmasi Penerimaan Pelanggan (Jendela 48 Jam)
                        </div>
                        <p class="text-[11px] text-sky-800">
                            Barang telah diserahkan pada {{ $booking->returned_at?->translatedFormat('d M Y, H:i') }} WIB. Pelanggan memiliki waktu hingga <strong>{{ $booking->auto_complete_at?->translatedFormat('d M Y, H:i') }} WIB</strong> untuk mengonfirmasi atau menyampaikan komplain. Jika tidak ada keluhan, sistem menyelesaikan pesanan otomatis.
                        </p>
                    </div>
                @elseif($booking->isCompleted())
                    <div class="p-4 rounded-2xl bg-emerald-100 text-emerald-900 text-xs font-medium flex items-center gap-2">
                        <i class="bi bi-patch-check-fill text-emerald-700 text-base"></i>
                        <span>Layanan selesai secara penuh. Barang telah dikonfirmasi diterima pada {{ $booking->customer_confirmed_at?->translatedFormat('d M Y, H:i') ?? $booking->actual_check_out_at?->translatedFormat('d M Y, H:i') }} WIB. Payout telah tercatat.</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
