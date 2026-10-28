@extends('layouts.admin')

@section('title', 'Pusat Mediasi & Sengketa (Disputes) - Admin')
@section('page_title', 'Pusat Mediasi & Penanganan Sengketa')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Regional Scope Banner -->
    <div class="bg-slate-900 rounded-2xl p-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-purple-600/30 text-purple-400 flex items-center justify-center text-sm font-bold">
                <i class="bi bi-shield-exclamation"></i>
            </span>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Cakupan Wilayah Sengketa</span>
                @if(auth()->user()->isSuperadmin())
                    <span class="font-bold text-white">Nasional (Seluruh Wilayah Indonesia & Kasus Wajib Eskalasi)</span>
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
        <form method="GET" action="{{ route('admin.disputes.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <select name="status" class="w-full sm:w-48 py-2.5 px-3.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-purple-600">
                <option value="">Semua Status Sengketa</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open (Menunggu Penanganan)</option>
                <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>Escalated (Superadmin)</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
            </select>

            <select name="category" class="w-full sm:w-64 py-2.5 px-3.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-purple-600">
                <option value="">Semua Kategori Masalah</option>
                @foreach(\App\Enums\DisputeCategory::cases() as $cat)
                    <option value="{{ $cat->value }}" {{ request('category') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                @endforeach
            </select>

            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 px-2 cursor-pointer">
                <input type="checkbox" name="escalated_only" value="1" {{ request('escalated_only') ? 'checked' : '' }} class="rounded text-purple-600 focus:ring-purple-500">
                <span>Hanya Kasus Eskalasi</span>
            </label>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition ml-auto">
                Filter
            </button>
        </form>
    </div>

    <!-- Disputes Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="py-4 px-5">ID & Tanggal</th>
                        <th class="py-4 px-5">Reservasi</th>
                        <th class="py-4 px-5">Pelanggan</th>
                        <th class="py-4 px-5">Mitra / Tempat</th>
                        <th class="py-4 px-5">Kategori Masalah</th>
                        <th class="py-4 px-5">Status & Eskalasi</th>
                        <th class="py-4 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($disputes as $dispute)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-5 font-mono">
                                <span class="font-bold text-slate-900">#DSP-{{ $dispute->id }}</span>
                                <span class="block font-sans text-[10px] text-slate-400">
                                    {{ $dispute->created_at->format('d M Y, H:i') }}
                                </span>
                            </td>
                            <td class="py-4 px-5 font-mono font-bold text-purple-700">
                                {{ $dispute->booking->booking_code }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="font-bold text-slate-900 block">{{ $dispute->customer->name }}</span>
                                <span class="text-[11px] text-slate-500">{{ $dispute->customer->phone ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-5">
                                <span class="font-bold text-slate-800 block">{{ $dispute->vendorProfile->business_name }}</span>
                                <span class="text-[11px] text-slate-500">{{ $dispute->booking->listing->city }}</span>
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $dispute->category->badgeClasses() }}">
                                    {{ $dispute->category->label() }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $dispute->status->badgeClasses() }}">
                                        {{ $dispute->status->label() }}
                                    </span>
                                    @if($dispute->is_escalated)
                                        <span class="block text-[10px] font-bold text-purple-700">
                                            <i class="bi bi-arrow-up-right-circle-fill"></i> Wajib Superadmin
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-5 text-right">
                                <a href="{{ route('admin.disputes.show', $dispute->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold transition text-xs">
                                    Periksa Berkas <i class="bi bi-chevron-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <i class="bi bi-shield-check text-3xl text-emerald-400 block mb-2"></i>
                                Tidak ada pengaduan atau sengketa aktif di wilayah ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($disputes->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $disputes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
