@extends('layouts.admin')

@section('title', 'Moderasi Listing - ' . $listing->title)
@section('page_title', 'Moderasi Tempat: ' . $listing->title)

@section('content')
<div class="max-w-4xl space-y-6">

    <div>
        <a href="{{ route('admin.listings.index') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-purple-600 transition">
            <i class="bi bi-arrow-left mr-1.5"></i> Kembali ke Antrian Moderasi Listing
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 sm:p-8 bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-bold text-purple-400 tracking-wider">{{ $listing->category->name ?? 'Loker Penitipan' }}</span>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-2xl font-extrabold text-white">{{ $listing->title }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $listing->status->badgeClasses() }}">
                        {{ $listing->status->label() }}
                    </span>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-xs text-slate-400 block">Mitra Pengelola</span>
                <span class="text-lg font-bold text-white">{{ $listing->vendorProfile->business_name }}</span>
                <span class="text-xs text-purple-400 block">{{ $listing->city ?? 'Jakarta' }}</span>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            
            <!-- Specs Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Tarif / Jam</span>
                    <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($listing->price_per_hour, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Tarif / Hari</span>
                    <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Kapasitas Total</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $listing->capacity_total }} Unit</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Jam Buka</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $listing->operational_hours ?? '24 Jam' }}</span>
                </div>
            </div>

            <!-- Address and Facilities -->
            <div class="space-y-3 text-xs sm:text-sm">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block text-xs uppercase font-bold mb-1">Alamat Fisik Lengkap</span>
                    <p class="font-medium text-slate-800">{{ $listing->address }}</p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block text-xs uppercase font-bold mb-1">Fasilitas Keamanan</span>
                    <div class="flex flex-wrap gap-2 pt-1">
                        @foreach($listing->facilities as $fac)
                            <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-semibold text-slate-700 text-xs">
                                <i class="bi bi-shield-check text-purple-600 mr-1"></i> {{ $fac->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                @if($listing->description)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-400 block text-xs uppercase font-bold mb-1">Deskripsi</span>
                        <p class="text-slate-700 leading-relaxed">{{ $listing->description }}</p>
                    </div>
                @endif

                @if($listing->rules)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-400 block text-xs uppercase font-bold mb-1">Aturan Penitipan</span>
                        <p class="text-slate-700 leading-relaxed">{{ $listing->rules }}</p>
                    </div>
                @endif
            </div>

            <!-- Moderation Actions -->
            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('catalog.show', $listing->slug) }}" target="_blank" class="text-xs font-semibold text-slate-500 hover:text-purple-600 transition flex items-center gap-1">
                    <i class="bi bi-box-arrow-up-right"></i> Pratinjau Tampilan Publik
                </a>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <!-- Reject Form -->
                    <form action="{{ route('admin.listings.reject', $listing->id) }}" method="POST" class="inline w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-white border border-rose-300 hover:bg-rose-50 text-rose-700 font-bold text-xs transition">
                            Tolak / Nonaktifkan
                        </button>
                    </form>

                    <!-- Publish Form -->
                    <form action="{{ route('admin.listings.publish', $listing->id) }}" method="POST" class="inline w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/25 transition flex items-center justify-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> Setujui &amp; Publikasikan ke Katalog
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
