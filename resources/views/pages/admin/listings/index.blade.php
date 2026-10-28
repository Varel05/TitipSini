@extends('layouts.admin')

@section('title', 'Moderasi Listing Tempat Penitipan - Admin')
@section('page_title', 'Moderasi Tempat Penitipan & Loker')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Regional Scope Banner -->
    <div class="bg-slate-900 rounded-2xl p-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-purple-600/30 text-purple-400 flex items-center justify-center text-sm font-bold">
                <i class="bi bi-geo-alt"></i>
            </span>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Cakupan Wilayah Moderasi</span>
                @if(auth()->user()->isSuperadmin())
                    <span class="font-bold text-white">Nasional (Seluruh Wilayah Operasional Indonesia)</span>
                @elseif($assignedRegion)
                    <span class="font-bold text-amber-300">{{ $assignedRegion->name }} ({{ $assignedRegion->code }})</span>
                @else
                    <span class="font-bold text-rose-400">Tidak ada wilayah yang ditugaskan ke akun Anda</span>
                @endif
            </div>
        </div>

        @if(!auth()->user()->isSuperadmin() && $assignedRegion)
            <div class="text-[11px] text-slate-300 bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700/60">
                Kota Cakupan: <span class="text-white font-medium">{{ implode(', ', $assignedRegion->cities ?? []) }}</span>
            </div>
        @endif
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto">
                <a href="{{ route('admin.listings.index') }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'all' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.listings.index', ['status' => 'submitted']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'submitted' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Menunggu Review ({{ $counts['submitted'] }})
                </a>
                <a href="{{ route('admin.listings.index', ['status' => 'published']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'published' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Tayang Publik ({{ $counts['published'] }})
                </a>
                <a href="{{ route('admin.listings.index', ['status' => 'rejected']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    Ditolak ({{ $counts['rejected'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.listings.index') }}" method="GET" class="w-full sm:w-72 flex items-center gap-2">
                @if($currentStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="relative w-full">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari judul, mitra, kota..."
                        class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 text-xs placeholder-slate-400">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <!-- Listings Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if($listings->isEmpty())
            <div class="text-center py-16">
                <i class="bi bi-shop text-3xl text-slate-300 mb-2 block"></i>
                <h4 class="font-bold text-slate-800 text-sm">Tidak ada tempat penitipan ditemukan</h4>
                <p class="text-xs text-slate-400 mt-1">Gunakan filter status lain atau ubah kata kunci pencarian Anda.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-6">Tempat &amp; Kategori</th>
                            <th class="py-3.5 px-6">Mitra Pengelola</th>
                            <th class="py-3.5 px-6">Tarif Sewa</th>
                            <th class="py-3.5 px-6">Kapasitas Slot</th>
                            <th class="py-3.5 px-6">Status Publik</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($listings as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 block text-sm">{{ $item->title }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $item->category->name ?? 'Loker' }} • {{ $item->city ?? 'Jakarta' }}</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-semibold text-slate-800 block">{{ $item->vendorProfile->business_name }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $item->vendorProfile->phone }}</span>
                                </td>

                                <td class="py-4 px-6 font-semibold">
                                    <span class="text-slate-900 block">Rp {{ number_format($item->price_per_day, 0, ',', '.') }} / hari</span>
                                    <span class="text-[11px] text-slate-500">Rp {{ number_format($item->price_per_hour, 0, ',', '.') }} / jam</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-800 block">{{ $item->capacity_available }} / {{ $item->capacity_total }} Slot</span>
                                    <span class="text-[10px] text-slate-400">Tersedia / Total</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $item->status->badgeClasses() }}">
                                        {{ $item->status->label() }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-right">
                                    <a href="{{ route('admin.listings.show', $item->id) }}" 
                                        class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs inline-flex items-center gap-1 transition">
                                        Moderasi &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $listings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
