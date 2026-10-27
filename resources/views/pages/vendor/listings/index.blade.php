@extends('layouts.vendor')

@section('title', 'Kelola Listing Tempat Penitipan - Vendor')
@section('page_title', 'Kelola Tempat Penitipan & Tarif')

@section('content')
<div class="space-y-6 max-w-7xl">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Tempat Penitipan / Loker Anda</h2>
            <p class="text-xs text-slate-500">Atur ketersediaan slot kapasitas, tarif sewa per jam/hari, dan jam operasional</p>
        </div>
    </div>

    <!-- Listings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($listings as $listing)
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 block">{{ $listing->category->name ?? 'Loker' }}</span>
                        <h3 class="text-base font-extrabold text-slate-900 mt-0.5">{{ $listing->title }}</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $listing->address }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $listing->status->label() }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tarif / Jam</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($listing->price_per_hour, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tarif / Hari</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Total Slot</span>
                        <span class="font-bold text-slate-900">{{ $listing->capacity_total }} Unit</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Slot Tersedia</span>
                        <span class="font-bold text-emerald-600">{{ $listing->capacity_available }} Unit</span>
                    </div>
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div><span class="text-slate-400">Jam Operasional:</span> <strong class="text-slate-800">{{ $listing->operational_hours ?? '24 Jam Nonstop' }}</strong></div>
                    <div><span class="text-slate-400">Fasilitas:</span> 
                        @foreach($listing->facilities as $fac)
                            <span class="inline-block bg-white border border-slate-200 rounded px-1.5 py-0.5 text-[10px] mr-1">{{ $fac->name }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('catalog.show', $listing->slug) }}" target="_blank" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition flex items-center gap-1">
                        <i class="bi bi-box-arrow-up-right"></i> Lihat Halaman Publik
                    </a>

                    <a href="{{ route('vendor.listings.edit', $listing->id) }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                        Edit Tarif &amp; Kapasitas &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 bg-white rounded-3xl border border-slate-200">
                <p class="text-sm text-slate-500">Belum ada listing yang terdaftar untuk akun vendor Anda.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
