@extends('layouts.vendor')

@section('title', 'Edit Listing - ' . $listing->title)
@section('page_title', 'Edit Tempat Penitipan')

@section('content')
<div class="max-w-2xl space-y-6">

    <div>
        <a href="{{ route('vendor.listings.index') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-indigo-600 transition">
            <i class="bi bi-arrow-left mr-1.5"></i> Kembali ke Daftar Listing
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Perbarui Detail &amp; Tarif Penitipan</h2>
            <p class="text-xs text-slate-500 mt-1">Perubahan tarif dan kapasitas akan langsung berlaku untuk pesanan baru.</p>
        </div>

        <form action="{{ route('vendor.listings.update', $listing->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul Listing Penitipan</label>
                <input id="title" name="title" type="text" required value="{{ old('title', $listing->title) }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                @error('title')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="price_per_hour" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tarif per Jam (Rp)</label>
                    <input id="price_per_hour" name="price_per_hour" type="number" required value="{{ old('price_per_hour', (int)$listing->price_per_hour) }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label for="price_per_day" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tarif per Hari (Rp)</label>
                    <input id="price_per_day" name="price_per_day" type="number" required value="{{ old('price_per_day', (int)$listing->price_per_day) }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="capacity_total" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Total Kapasitas Slot (Unit)</label>
                    <input id="capacity_total" name="capacity_total" type="number" required value="{{ old('capacity_total', $listing->capacity_total) }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <span class="text-[11px] text-slate-400 mt-1 block">Saat ini tersedia: {{ $listing->capacity_available }} unit</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jam Operasional</label>
                    <div class="flex items-center gap-2">
                        <input name="operational_open" type="text" value="06:00" class="w-full px-3 py-3 rounded-xl border border-slate-200 text-sm text-center" placeholder="06:00">
                        <span class="text-xs text-slate-400">s/d</span>
                        <input name="operational_close" type="text" value="23:00" class="w-full px-3 py-3 rounded-xl border border-slate-200 text-sm text-center" placeholder="23:00">
                    </div>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Fasilitas &amp; Keamanan</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('description', $listing->description) }}</textarea>
            </div>

            <div>
                <label for="rules" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Aturan Penitipan</label>
                <textarea id="rules" name="rules" rows="3"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('rules', $listing->rules) }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('vendor.listings.index') }}" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
