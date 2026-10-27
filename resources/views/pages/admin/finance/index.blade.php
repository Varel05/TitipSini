@extends('layouts.admin')

@section('title', 'Rekonsiliasi Finansial & Payout - Admin')
@section('page_title', 'Rekonsiliasi Finansial & Penyaluran Dana')

@section('content')
<div class="space-y-8 max-w-7xl">

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Total Pembayaran Masuk</span>
            <div class="text-3xl font-black text-slate-900">Rp {{ number_format($totalGrossCollected, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Bruto via Gateway Xendit</span>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Komisi TitipSini (20%)</span>
            <div class="text-3xl font-black text-purple-600">Rp {{ number_format($totalPlatformCommission, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Pendapatan bersih platform</span>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Telah Disalurkan (80%)</span>
            <div class="text-3xl font-black text-emerald-600">Rp {{ number_format($totalVendorDisbursed, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Sukses payout ke bank vendor</span>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Menunggu Pencairan</span>
            <div class="text-3xl font-black text-amber-600">Rp {{ number_format($totalPendingSettlement, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Pending konfirmasi serah-terima</span>
        </div>
    </div>

    <!-- Main Reconciliation Tabs & Tables -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden space-y-4">
        
        <!-- Header & Tabs -->
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Buku Besar Transaksi &amp; Payout (Ledger)</h3>
                <p class="text-xs text-slate-500">Pencocokan invoice pembayaran pelanggan dan porsi penyaluran 80% mitra</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.finance.index', ['tab' => 'settlements']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'settlements' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Settlement Mitra (80%)
                </a>
                <a href="{{ route('admin.finance.index', ['tab' => 'payments']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'payments' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Invoice Pelanggan (Gross)
                </a>
            </div>
        </div>

        @if($tab === 'settlements')
            <!-- Settlements Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-6">ID &amp; Tanggal</th>
                            <th class="py-3 px-6">Mitra Penerima</th>
                            <th class="py-3 px-6">Rekening Tujuan</th>
                            <th class="py-3 px-6">Nominal Bersih (80%)</th>
                            <th class="py-3 px-6">Ref Payout Xendit</th>
                            <th class="py-3 px-6">Status</th>
                            <th class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($settlements as $s)
                            <tr class="hover:bg-slate-50">
                                <td class="py-4 px-6 font-mono">
                                    <span class="font-bold text-slate-900 block">SETTLE-{{ $s->id }}</span>
                                    <span class="text-[11px] text-slate-400 font-sans">{{ $s->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 block">{{ $s->vendorProfile->business_name }}</span>
                                    <span class="text-[11px] text-slate-500 font-mono">Booking: {{ $s->booking->booking_code }}</span>
                                </td>

                                <td class="py-4 px-6 font-mono">
                                    <span class="font-semibold text-slate-800 block">{{ $s->vendorProfile->bank_name ?? 'BCA' }} - {{ $s->vendorProfile->bank_account_number ?? '1234567890' }}</span>
                                    <span class="text-[11px] text-slate-400 font-sans">{{ $s->vendorProfile->bank_account_holder ?? $s->vendorProfile->business_name }}</span>
                                </td>

                                <td class="py-4 px-6 font-bold text-emerald-600 text-sm">
                                    Rp {{ number_format($s->amount, 0, ',', '.') }}
                                </td>

                                <td class="py-4 px-6 font-mono text-[11px] text-slate-500">
                                    {{ $s->payout_reference ?? 'Belum Diproses' }}
                                </td>

                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $s->status->badgeClasses() }}">
                                        {{ $s->status->label() }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-right">
                                    @if($s->status->value === 'pending')
                                        <form action="{{ route('admin.finance.retry_payout', $s->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                                                Cairkan &rarr;
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-emerald-600 font-semibold"><i class="bi bi-check2"></i> Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada mutasi settlement vendor tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $settlements->links() }}
            </div>
        @else
            <!-- Payments Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-6">Referensi Invoice</th>
                            <th class="py-3 px-6">Pelanggan</th>
                            <th class="py-3 px-6">Tempat Penitipan</th>
                            <th class="py-3 px-6">Metode</th>
                            <th class="py-3 px-6">Nominal Bruto</th>
                            <th class="py-3 px-6">Status Bayar</th>
                            <th class="py-3 px-6">Waktu Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($payments as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="py-4 px-6 font-mono">
                                    <span class="font-bold text-slate-900 block">{{ $p->payment_reference }}</span>
                                    <span class="text-[11px] text-indigo-600 font-semibold">Booking: {{ $p->booking->booking_code }}</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 block">{{ $p->booking->customer->name }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $p->booking->customer->phone }}</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-medium text-slate-800 block">{{ $p->booking->listing->title }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $p->booking->listing->vendorProfile->business_name }}</span>
                                </td>

                                <td class="py-4 px-6 font-mono uppercase text-xs">
                                    <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded font-semibold text-slate-700">
                                        {{ $p->channel }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 font-bold text-slate-900 text-sm">
                                    Rp {{ number_format($p->amount, 0, ',', '.') }}
                                </td>

                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $p->status->badgeClasses() }}">
                                        {{ $p->status->label() }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-slate-500 text-[11px]">
                                    {{ $p->paid_at ? $p->paid_at->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada transaksi pembayaran masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
