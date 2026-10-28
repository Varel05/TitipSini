@extends('layouts.admin')

@section('title', 'Manajemen Booking & Siklus QRIS - Admin')
@section('page_title', 'Manajemen Reservasi & Siklus Pembayaran')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Regional Scope Banner -->
    <div class="bg-slate-900 rounded-2xl p-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-purple-600/30 text-purple-400 flex items-center justify-center text-sm font-bold">
                <i class="bi bi-geo-alt"></i>
            </span>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Cakupan Wilayah Operasional</span>
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
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode booking, nama customer, atau mitra..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-purple-600 focus:border-transparent outline-none transition">
            </div>

            <select name="status" class="w-full sm:w-48 py-2.5 px-3.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-purple-600">
                <option value="">Semua Status</option>
                <option value="created" {{ request('status') === 'created' ? 'selected' : '' }}>Created (Reservasi Baru)</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed (Diterima)</option>
                <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Checked In (Disimpan)</option>
                <option value="retrieval_requested" {{ request('status') === 'retrieval_requested' ? 'selected' : '' }}>Retrieval Requested (Ambil)</option>
                <option value="returned_by_vendor" {{ request('status') === 'returned_by_vendor' ? 'selected' : '' }}>Returned By Vendor</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled (Batal)</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="py-4 px-5">Kode Booking</th>
                        <th class="py-4 px-5">Customer</th>
                        <th class="py-4 px-5">Mitra / Lokasi</th>
                        <th class="py-4 px-5">Status Layanan</th>
                        <th class="py-4 px-5">Siklus QRIS (Policy 10.2)</th>
                        <th class="py-4 px-5">Total Tagihan</th>
                        <th class="py-4 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-5 font-mono font-bold text-purple-700">
                                {{ $booking->booking_code }}
                                <span class="block font-sans font-normal text-[10px] text-slate-400">
                                    {{ $booking->created_at->format('d M Y, H:i') }}
                                </span>
                            </td>
                            <td class="py-4 px-5 font-medium text-slate-900">
                                {{ $booking->customer?->name ?? 'Pelanggan Tamu' }}
                                <span class="block text-[11px] text-slate-500 font-mono">
                                    {{ $booking->getCustomerPhoneDisplay() }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                <span class="font-bold text-slate-800 block">{{ $booking->vendorProfile?->business_name ?? '-' }}</span>
                                <span class="text-[11px] text-slate-500">{{ $booking->listing?->title }} • {{ $booking->listing?->city }}</span>
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $booking->status->badgeClasses() }}">
                                    {{ $booking->status->label() }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 font-bold text-slate-700">
                                        <i class="bi bi-arrow-repeat text-purple-600"></i> Siklus #{{ $booking->current_payment_cycle }}
                                    </span>
                                    @php
                                        $cycleCount = $booking->getCurrentCycleAttemptsCount();
                                        $isExceeded = $booking->isPaymentAttemptLimitExceeded();
                                    @endphp
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $isExceeded ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-600' }}">
                                            Percobaan: {{ $cycleCount }}/3
                                        </span>
                                        @if($isExceeded)
                                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-bold text-slate-900">
                                Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                <a href="{{ route('admin.bookings.show', $booking->booking_code) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold transition text-xs">
                                    Detail & QRIS <i class="bi bi-chevron-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <i class="bi bi-inbox text-3xl text-slate-300 block mb-2"></i>
                                Tidak ada data reservasi yang ditemukan di wilayah ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
