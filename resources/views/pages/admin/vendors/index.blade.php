@extends('layouts.admin')

@section('title', 'Verifikasi KYC Mitra Vendor - Admin')
@section('page_title', 'Verifikasi KYC & Manajemen Mitra')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Filters & Search -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto">
                <a href="{{ route('admin.vendors.index') }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'all' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.vendors.index', ['status' => 'pending']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Menunggu Verifikasi ({{ $counts['pending'] }})
                </a>
                <a href="{{ route('admin.vendors.index', ['status' => 'verified']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'verified' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Terverifikasi ({{ $counts['verified'] }})
                </a>
                <a href="{{ route('admin.vendors.index', ['status' => 'rejected']) }}" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    Ditolak ({{ $counts['rejected'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.vendors.index') }}" method="GET" class="w-full sm:w-72 flex items-center gap-2">
                @if($currentStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="relative w-full">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari usaha, nama, kota..."
                        class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 text-xs placeholder-slate-400">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <!-- Vendor Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if($vendors->isEmpty())
            <div class="text-center py-16">
                <i class="bi bi-person-x text-3xl text-slate-300 mb-2 block"></i>
                <h4 class="font-bold text-slate-800 text-sm">Tidak ada mitra vendor ditemukan</h4>
                <p class="text-xs text-slate-400 mt-1">Coba gunakan filter status lain atau ubah kata kunci pencarian.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-6">Nama Usaha / Loker</th>
                            <th class="py-3.5 px-6">Pemilik Akun</th>
                            <th class="py-3.5 px-6">Lokasi &amp; Kota</th>
                            <th class="py-3.5 px-6">Rekening Bank</th>
                            <th class="py-3.5 px-6">Status KYC</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($vendors as $v)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 block text-sm">{{ $v->business_name }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono">ID: {{ $v->id }} • {{ $v->listings->count() }} Listing</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-semibold text-slate-800 block">{{ $v->user->name }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $v->user->phone }} • {{ $v->user->email }}</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-medium text-slate-800 block">{{ $v->city ?? 'Jakarta' }}</span>
                                    <span class="text-[11px] text-slate-400 line-clamp-1 max-w-xs">{{ $v->address }}</span>
                                </td>

                                <td class="py-4 px-6 font-mono">
                                    <span class="font-bold text-slate-800 block">{{ $v->bank_name ?? 'BCA' }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $v->bank_account_number ?? '1234567890' }}</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $v->verification_status->badgeClasses() }}">
                                        {{ $v->verification_status->label() }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-right">
                                    <a href="{{ route('admin.vendors.show', $v->id) }}" 
                                        class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs inline-flex items-center gap-1 transition">
                                        Review KYC &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
