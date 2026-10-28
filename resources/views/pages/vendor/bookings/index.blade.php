@extends('layouts.vendor')

@section('title', 'Operasional Penitipan - Vendor')
@section('page_title', 'Operasional Penitipan Barang')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Filters & Search -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto">
                <a href="{{ route('vendor.bookings.index') }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua
                </a>
                <a href="{{ route('vendor.bookings.index', ['status' => 'confirmed']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'confirmed' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                    Menunggu Drop
                </a>
                <a href="{{ route('vendor.bookings.index', ['status' => 'checked_in']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'checked_in' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' }}">
                    Tersimpan Tersegel
                </a>
                <a href="{{ route('vendor.bookings.index', ['status' => 'retrieval_requested']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'retrieval_requested' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                    Siap Ambil
                </a>
                <a href="{{ route('vendor.bookings.index', ['status' => 'completed']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'completed' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Selesai
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('vendor.bookings.index') }}" method="GET" class="w-full sm:w-72 flex items-center gap-2">
                @if($currentStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="relative w-full">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode, nama, no segel..."
                        class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs placeholder-slate-400">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <!-- Booking List Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if($bookings->isEmpty())
            <div class="text-center py-16">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <i class="bi bi-inbox text-xl"></i>
                </div>
                <h4 class="font-bold text-slate-800 text-sm">Tidak ada pesanan ditemukan</h4>
                <p class="text-xs text-slate-400 mt-1">Gunakan filter status lain atau ubah kata kunci pencarian Anda.</p>
            </div>
        @else
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Kode &amp; Tanggal</th>
                            <th class="py-3 px-4 sm:px-5">Pelanggan</th>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Barang &amp; Durasi</th>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Kode Segel Keamanan</th>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Porsi Vendor (80%)</th>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Status</th>
                            <th class="py-3 px-4 sm:px-5 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($bookings as $b)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 sm:px-5 font-mono whitespace-nowrap">
                                    <span class="font-bold text-slate-900 block text-xs">{{ $b->booking_code }}</span>
                                    <span class="text-[11px] text-slate-400 font-sans block">{{ $b->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 max-w-[150px]">
                                    <span class="font-bold text-slate-900 block truncate">{{ $b->customer->name }}</span>
                                    <span class="text-[11px] text-slate-500 block truncate font-mono">{{ $b->customer->phone }}</span>
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 block">{{ $b->total_items }} Unit Barang</span>
                                    <span class="text-[11px] text-slate-500">{{ $b->duration_units }} {{ $b->duration_type === 'day' ? 'Hari' : 'Jam' }}</span>
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 font-mono whitespace-nowrap">
                                    @if($b->security_seal_code)
                                        <span class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                                            <i class="bi bi-shield-lock-fill"></i> {{ $b->security_seal_code }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Belum Tersegel</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 font-semibold whitespace-nowrap">
                                    <span class="text-emerald-600 font-bold block text-sm">Rp {{ number_format($b->vendor_earnings, 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-slate-400">Total: Rp {{ number_format($b->total_amount, 0, ',', '.') }}</span>
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap shadow-sm {{ $b->status->badgeClasses() }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $b->status->dotColorClass() }}"></span>
                                        {{ $b->status->label() }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 text-right whitespace-nowrap">
                                    <a href="{{ route('vendor.bookings.show', $b->booking_code) }}" 
                                        class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs inline-flex items-center gap-1 transition">
                                        Proses &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
