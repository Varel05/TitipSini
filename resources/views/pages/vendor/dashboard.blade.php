@extends('layouts.vendor')

@section('title', 'Dashboard Operasional Mitra')
@section('page_title', 'Dashboard Operasional')

@section('content')
<div class="space-y-8 max-w-7xl">

    <!-- Hero Profile Status Card -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg border border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $vendor->verification_status->badgeClasses() }}">
                        <i class="bi bi-shield-check"></i> {{ $vendor->verification_status->label() }}
                    </span>
                    <span class="text-xs text-slate-400 font-mono">ID: {{ $vendor->id }}</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $vendor->business_name }}</h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">{{ $vendor->address }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('vendor.bookings.index') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-2">
                    <i class="bi bi-box-seam"></i> Buka Antrian Penitipan
                </a>
                <a href="{{ route('vendor.listings.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs transition">
                    Kelola Tarif & Jam Buka
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Key Performance Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Active Bookings -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider">Booking Aktif</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="bi bi-calendar2-check-fill"></i>
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $activeBookingsCount }}</div>
            <span class="text-xs text-slate-500 mt-1 block">Pesanan dalam proses penitipan</span>
        </div>

        <!-- Metric 2: Currently Stored Items -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider">Barang Tersimpan</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                    <i class="bi bi-shield-lock-fill"></i>
                </span>
            </div>
            <div class="text-3xl font-black text-indigo-600">{{ $storedItemsCount }}</div>
            <span class="text-xs text-slate-500 mt-1 block">Barang bersegel di lokasi</span>
        </div>

        <!-- Metric 3: Menunggu Pengambilan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider">Siap Diambil</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
                    <i class="bi bi-bell-fill"></i>
                </span>
            </div>
            <div class="text-3xl font-black text-purple-600">{{ $pendingRetrievalCount }}</div>
            <span class="text-xs text-slate-500 mt-1 block">Pelanggan meminta serah terima</span>
        </div>

        <!-- Metric 4: Lifetime Earnings Settled -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider">Pendapatan Bersih (80%)</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="bi bi-cash-stack"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-500 mt-1 block">Pending: Rp {{ number_format($pendingPayout, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Priority Operational Action Queue -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Antrian Tugas Operasional Terkini</h3>
                <p class="text-xs text-slate-500">Tindakan fisik yang perlu dilakukan mitra (Pemasangan segel atau serah terima)</p>
            </div>
            <a href="{{ route('vendor.bookings.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                Lihat Semua &rarr;
            </a>
        </div>

        @if($priorityBookings->isEmpty())
            <div class="text-center py-12">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <i class="bi bi-inbox text-xl"></i>
                </div>
                <h4 class="font-bold text-slate-700 text-sm">Belum ada tugas operasional</h4>
                <p class="text-xs text-slate-400 mt-1">Saat pelanggan memesan atau meminta pengambilan barang, daftar akan muncul di sini.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($priorityBookings as $b)
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-sm text-slate-900">{{ $b->booking_code }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $b->status->badgeClasses() }}">
                                    {{ $b->status->label() }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600">
                                Pelanggan: <strong>{{ $b->customer->name }}</strong> ({{ $b->customer->phone }}) • 
                                {{ $b->total_items }} Unit Barang • Rencana: {{ $b->check_in_plan_at->translatedFormat('d M, H:i') }}
                            </p>
                            @if($b->security_seal_code)
                                <div class="text-[11px] text-slate-500 flex items-center gap-1.5 font-mono">
                                    <i class="bi bi-shield-check text-emerald-600"></i> Segel: {{ $b->security_seal_code }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="{{ route('vendor.bookings.show', $b->booking_code) }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                                Buka Lembar Kerja &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Active Listings Overview -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Tempat Penitipan / Loker Anda</h3>
                <p class="text-xs text-slate-500">Kapasitas penyimpanan dan tarif aktif</p>
            </div>
            <a href="{{ route('vendor.listings.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                Kelola Detail &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($listings as $item)
                <div class="p-5 rounded-2xl border border-slate-200 hover:border-slate-300 transition space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-indigo-600 tracking-wider block">{{ $item->category->name ?? 'Loker' }}</span>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $item->title }}</h4>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $item->status->label() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 pt-2 border-t border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Tarif per Jam:</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($item->price_per_hour, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Tarif per Hari:</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Kapasitas Total:</span>
                            <span class="font-bold text-slate-900">{{ $item->capacity_total }} Unit</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Slot Tersedia:</span>
                            <span class="font-bold text-emerald-600">{{ $item->capacity_available }} Unit</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
