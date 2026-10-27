@extends('layouts.app')

@section('title', 'Riwayat Pesanan Penitipan — TitipSini')

@section('content')
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Riwayat Pesanan Penitipan</h1>
                    <p class="text-sm text-slate-500">Lacak status barang dan akses tiket digital penitipan Anda.</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition-all">
                    <i class="bi bi-plus-lg"></i> Penitipan Baru
                </a>
            </div>

            @if($bookings->isNotEmpty())
                <div class="space-y-4">
                    @foreach($bookings as $booking)
                        <div class="p-6 bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold text-sm text-slate-900">{{ $booking->booking_code }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $booking->status->badgeClasses() }}">
                                        {{ $booking->status->label() }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-base text-slate-900">
                                    <a href="{{ route('booking.show', $booking->booking_code) }}" class="hover:text-indigo-600">
                                        {{ $booking->listing->title }}
                                    </a>
                                </h3>
                                <p class="text-xs text-slate-500 flex items-center gap-2">
                                    <span><i class="bi bi-geo-alt-fill text-indigo-600"></i> {{ $booking->listing->city }}</span>
                                    <span>•</span>
                                    <span>{{ $booking->total_items }} Unit Barang</span>
                                    <span>•</span>
                                    <span>Rencana: {{ $booking->check_in_plan_at->translatedFormat('d M Y, H:i') }}</span>
                                </p>
                            </div>

                            <div class="flex items-center justify-between md:justify-end gap-6 w-full md:w-auto border-t md:border-t-0 pt-4 md:pt-0 border-slate-100">
                                <div class="text-left md:text-right">
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Total Pelunasan</span>
                                    <span class="text-base font-black text-indigo-600">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('booking.show', $booking->booking_code) }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-xs font-semibold shadow-sm transition-all whitespace-nowrap">
                                    Buka Tiket
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-4">
                        {{ $bookings->links() }}
                    </div>
                </div>
            @else
                <div class="p-12 text-center rounded-3xl bg-white border border-slate-200 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h3 class="font-bold text-lg text-slate-900">Belum Ada Riwayat Penitipan</h3>
                        <p class="text-xs text-slate-500">Anda belum memiliki pesanan aktif. Mulai titip koper dan barang bawaan Anda sekarang dengan aman.</p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow-sm">
                            <i class="bi bi-compass"></i> Cari Tempat Penitipan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
