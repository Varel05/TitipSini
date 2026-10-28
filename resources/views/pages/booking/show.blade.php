@extends('layouts.app')

@section('title', 'Tiket Penitipan ' . $booking->booking_code . ' — TitipSini')

@section('content')
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-indigo-600">Beranda</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <a href="{{ route('booking.index') }}" class="hover:text-indigo-600">Riwayat Pesanan</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-bold">{{ $booking->booking_code }}</span>
            </div>

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                    <i class="bi bi-check-circle-fill text-emerald-600 text-lg flex-shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center gap-3">
                    <i class="bi bi-info-circle-fill text-blue-600 text-lg flex-shrink-0"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Main Digital Pass / Ticket -->
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-lg overflow-hidden">
                <!-- Ticket Header -->
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white relative">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-400">Tiket Digital Penitipan Resmi</span>
                            <h1 class="text-2xl sm:text-3xl font-black font-mono tracking-tight">{{ $booking->booking_code }}</h1>
                            <p class="text-xs text-slate-300">Dibuat pada {{ $booking->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $booking->status->badgeClasses() }} backdrop-blur-md">
                                <span class="w-2 h-2 rounded-full bg-current"></span>
                                {{ $booking->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stepper Progress Timeline -->
                <div class="p-6 bg-slate-50 border-b border-slate-200">
                    <div class="grid grid-cols-4 gap-2 text-center text-xs">
                        <!-- Step 1: Reservasi -->
                        <div class="space-y-1">
                            <div class="w-8 h-8 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                <i class="bi bi-check2"></i>
                            </div>
                            <span class="font-bold text-slate-800 block text-[11px]">Dipesan</span>
                            <span class="text-[10px] text-slate-400">Gratis (Rp 0)</span>
                        </div>

                        <!-- Step 2: Check-In -->
                        <div class="space-y-1">
                            <div class="w-8 h-8 mx-auto rounded-full {{ in_array($booking->status->value, ['checked_in', 'retrieval_requested', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center font-bold text-xs shadow-sm">
                                @if(in_array($booking->status->value, ['checked_in', 'retrieval_requested', 'completed']))
                                    <i class="bi bi-check2"></i>
                                @else
                                    2
                                @endif
                            </div>
                            <span class="font-bold {{ in_array($booking->status->value, ['checked_in', 'retrieval_requested', 'completed']) ? 'text-slate-800' : 'text-slate-400' }} block text-[11px]">Drop &amp; Segel</span>
                            <span class="text-[10px] text-slate-400">CCTV &amp; Tag Barcode</span>
                        </div>

                        <!-- Step 3: Bayar Saat Ambil -->
                        <div class="space-y-1">
                            <div class="w-8 h-8 mx-auto rounded-full {{ in_array($booking->status->value, ['retrieval_requested', 'completed']) ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center font-bold text-xs shadow-sm">
                                @if($booking->status->value === 'completed')
                                    <i class="bi bi-check2"></i>
                                @else
                                    3
                                @endif
                            </div>
                            <span class="font-bold {{ in_array($booking->status->value, ['retrieval_requested', 'completed']) ? 'text-indigo-600' : 'text-slate-400' }} block text-[11px]">Bayar via Gateway</span>
                            <span class="text-[10px] text-slate-400">QRIS / VA Xendit</span>
                        </div>

                        <!-- Step 4: Selesai -->
                        <div class="space-y-1">
                            <div class="w-8 h-8 mx-auto rounded-full {{ $booking->status->value === 'completed' ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center font-bold text-xs shadow-sm">
                                @if($booking->status->value === 'completed')
                                    <i class="bi bi-check2"></i>
                                @else
                                    4
                                @endif
                            </div>
                            <span class="font-bold {{ $booking->status->value === 'completed' ? 'text-emerald-700' : 'text-slate-400' }} block text-[11px]">Selesai</span>
                            <span class="text-[10px] text-slate-400">Serah Terima Tuntas</span>
                        </div>
                    </div>
                </div>

                <!-- Ticket Content Body -->
                <div class="p-6 sm:p-8 space-y-6">
                    
                    <!-- Seal Barcode Card (if checked in) -->
                    @if($booking->security_seal_code)
                        <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-2xl shadow-sm">
                                    <i class="bi bi-qr-code-scan"></i>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Kode Segel Anti-Buka (Tamper Seal)</span>
                                    <h3 class="font-mono text-lg font-black text-slate-900">{{ $booking->security_seal_code }}</h3>
                                    <p class="text-xs text-slate-500">Tercatat pada pemeriksaan fisik barang di lokasi.</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                <i class="bi bi-shield-lock-fill"></i> Terproteksi
                            </span>
                        </div>
                    @endif

                    <!-- Detail Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm">
                        <!-- Left Specs -->
                        <div class="space-y-3">
                            <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-2">Informasi Mitra &amp; Lokasi</h3>
                            <div class="space-y-2 text-slate-600">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Tempat Penitipan</span>
                                    <span class="font-bold text-slate-900 text-right">{{ $booking->listing->title }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Mitra Pengelola</span>
                                    <span class="font-medium text-slate-800">{{ $booking->vendorProfile->business_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Alamat</span>
                                    <span class="font-medium text-slate-800 text-right max-w-xs">{{ $booking->listing->address }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400">Telepon / WhatsApp Mitra</span>
                                    @if($booking->canViewContacts())
                                        <span class="font-bold text-slate-900 font-mono flex items-center gap-1">
                                            <i class="bi bi-whatsapp text-emerald-600"></i> {{ $booking->vendorProfile->phone }}
                                        </span>
                                    @else
                                        <span class="text-[11px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                            <i class="bi bi-eye-slash-fill"></i> {{ $booking->getVendorPhoneDisplay() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right Specs -->
                        <div class="space-y-3">
                            <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-2">Rincian Penitipan</h3>
                            <div class="space-y-2 text-slate-600">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Nama Pelanggan</span>
                                    <span class="font-bold text-slate-900">{{ $booking->customer->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Rencana Drop</span>
                                    <span class="font-medium text-slate-800">{{ $booking->check_in_plan_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Durasi Booking</span>
                                    <span class="font-medium text-slate-800">{{ $booking->duration_units }} {{ $booking->duration_type === 'day' ? 'Hari' : 'Jam' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Total Barang</span>
                                    <span class="font-medium text-slate-800">{{ $booking->total_items }} Unit</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="pt-2 border-t border-slate-100 space-y-3">
                        <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider text-slate-500">Daftar Barang yang Dititipkan</h3>
                        <div class="divide-y divide-slate-100 bg-slate-50 rounded-2xl p-4">
                            @foreach($booking->items as $item)
                                <div class="py-2 first:pt-0 last:pb-0 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-800 flex items-center gap-2">
                                        <i class="bi bi-box-seam text-indigo-600"></i> {{ $item->item_name }}
                                    </span>
                                    <span class="text-slate-500 font-mono uppercase bg-white px-2 py-0.5 rounded border border-slate-200">
                                        Ukuran: {{ $item->item_size }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Pricing & Settlement Box -->
                    <div class="p-6 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-3">
                        <div class="flex justify-between text-xs text-slate-600">
                            <span>Biaya Penitipan ({{ $booking->duration_units }} {{ $booking->duration_type === 'day' ? 'Hari' : 'Jam' }})</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($booking->storage_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-500">
                            <span>Asuransi TitipSini (s.d Rp 10 Juta)</span>
                            <span class="font-bold text-emerald-600">GRATIS</span>
                        </div>
                        <div class="border-t border-indigo-200/80 pt-3 flex justify-between items-baseline">
                            <div>
                                <span class="text-sm font-black text-indigo-950 block">Total Pelunasan Saat Ambil</span>
                                <span class="text-[11px] text-slate-500 font-normal">Metode: QRIS / Virtual Account Xendit</span>
                            </div>
                            <span class="text-2xl font-black text-indigo-600">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                        </div>

                        @if($booking->isCompleted() && $booking->vendorSettlement)
                            <div class="pt-3 border-t border-indigo-200/80 text-xs text-emerald-800 flex items-center justify-between">
                                <span class="flex items-center gap-1.5"><i class="bi bi-check-circle-fill text-emerald-600"></i> Settlement Mitra Terjadwal</span>
                                <span class="font-mono font-bold">Porsi Mitra: Rp {{ number_format($booking->vendor_earnings, 0, ',', '.') }} (80%)</span>
                            </div>
                        @endif
                    </div>

                    <!-- Interactive Lifecycle Actions Area -->
                    <div class="pt-4 border-t border-slate-100 space-y-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Aksi Status Penitipan</span>

                        <!-- Action: When Confirmed (Ready to drop & check in) -->
                        @if($booking->status->value === 'confirmed')
                            <div class="p-4 rounded-2xl bg-slate-100 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div class="text-xs text-slate-600">
                                    <strong>Status: Menunggu Kedatangan Anda di Lokasi.</strong> Datangi alamat mitra dan tunjukkan tiket ini.
                                </div>
                                <form action="{{ route('booking.simulate_checkin', $booking->booking_code) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs whitespace-nowrap shadow-sm">
                                        <i class="bi bi-qr-code"></i> Konfirmasi Drop &amp; Pasang Segel
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Action: When Checked In (Goods safely stored) -->
                        @if($booking->status->value === 'checked_in')
                            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div class="text-xs text-indigo-900">
                                    <strong>Barang Anda sedang tersimpan aman.</strong> Klik tombol ini ketika Anda sudah tiba di lokasi mitra untuk mengambil barang.
                                </div>
                                <form action="{{ route('booking.retrieval', $booking->booking_code) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs whitespace-nowrap shadow-md shadow-indigo-600/30">
                                        <i class="bi bi-box-arrow-up-right"></i> Saya Mau Ambil Barang Sekarang
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Action: When Retrieval Requested (Needs Payment via Xendit) -->
                        @if($booking->status->value === 'retrieval_requested')
                            <div class="p-6 rounded-2xl bg-purple-50 border-2 border-purple-200 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-sm text-purple-950">Pelunasan Pembayaran Pengambilan Barang</h4>
                                        <p class="text-xs text-purple-800">Selesaikan pembayaran online berikut untuk menyelesaikan verifikasi dan mengambil barang Anda:</p>
                                    </div>
                                    <span class="text-lg font-black text-purple-700">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 pt-2">
                                    <a href="{{ route('booking.pay', $booking->booking_code) }}" class="px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/25 flex items-center justify-center gap-2 transition active:scale-95">
                                        <i class="bi bi-credit-card-2-front"></i> Bayar Sekarang (Xendit Checkout)
                                    </a>

                                    <form action="{{ route('booking.simulate_pay', $booking->booking_code) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 transition active:scale-95">
                                            <i class="bi bi-lightning-charge-fill"></i> Simulasi Bayar Instan (Demo)
                                        </button>
                                    </form>

                                    <a href="{{ route('booking.checkout_simulation', $booking->booking_code) }}" class="px-4 py-3 rounded-xl bg-white border border-purple-200 hover:bg-purple-100 text-purple-800 font-bold text-xs transition">
                                        <i class="bi bi-display"></i> Preview Checkout Portal
                                    </a>
                                </div>
                                <span class="text-[11px] text-purple-700 block">Dukungan: QRIS (GoPay, OVO, Dana), BCA/Mandiri/BRI Virtual Account. Mitra akan menerima notifikasi pelunasan seketika.</span>
                            </div>
                        @endif

                        <!-- Action: When Goods Returned by Vendor (Awaiting Customer Confirmation 48h - Policy 8.2) -->
                        @if($booking->status->value === 'returned_by_vendor')
                            <div class="p-6 rounded-2xl bg-sky-50 border-2 border-sky-300 space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center text-xl flex-shrink-0">
                                        <i class="bi bi-box2-heart-fill"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-sky-950">Mitra Telah Menyerahkan Barang Kembali</h4>
                                        <p class="text-xs text-sky-800">
                                            Mohon periksa kondisi fisik dan kelengkapan barang Anda. Sesuai Kebijakan 8.2, Anda memiliki waktu 48 jam (hingga {{ $booking->auto_complete_at?->translatedFormat('d M Y, H:i') }} WIB) untuk mengonfirmasi atau mengajukan keluhan.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 pt-2">
                                    <form action="{{ route('booking.confirm_return', $booking->booking_code) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md shadow-sky-600/25 flex items-center gap-2 transition active:scale-95">
                                            <i class="bi bi-check2-circle text-base"></i> Konfirmasi Barang Telah Diterima dengan Baik
                                        </button>
                                    </form>

                                    <a href="{{ route('faq') }}" class="px-4 py-3 rounded-xl bg-white border border-sky-300 text-sky-800 font-bold text-xs hover:bg-sky-100 transition">
                                        <i class="bi bi-question-circle"></i> Butuh Bantuan / Komplain
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Action: When Completed -->
                        @if($booking->status->value === 'completed')
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-3">
                                <i class="bi bi-patch-check-fill text-emerald-600 text-xl flex-shrink-0"></i>
                                <div>
                                    <strong class="block text-sm">Penitipan Telah Selesai Secara Sukses!</strong>
                                    <span>Barang telah diserah-terimakan kembali dan dana settlement otomatis diteruskan ke mitra. Terima kasih telah menggunakan TitipSini.</span>
                                </div>
                            </div>
                        @endif

                        <!-- Action: Cancel Button (if allowed) -->
                        @if($booking->status->canBeCancelled())
                            <div class="pt-2">
                                <form action="{{ route('booking.cancel', $booking->booking_code) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?');">
                                    @csrf
                                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-medium underline">
                                        Batalkan Pemesanan Ini
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Back to Catalog -->
            <div class="text-center pt-4">
                <a href="{{ route('catalog.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600">
                    &larr; Kembali ke Katalog Penitipan
                </a>
            </div>
        </div>
    </div>
@endsection
