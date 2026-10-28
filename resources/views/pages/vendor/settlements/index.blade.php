@extends('layouts.vendor')

@section('title', 'Settlement & Payout Mitra (80%) - Vendor')
@section('page_title', 'Settlement & Riwayat Pencairan Dana')

@section('content')
<div class="space-y-8 max-w-7xl">

    <!-- Financial Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Settled -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Total Telah Dicairkan</span>
            <div class="text-3xl font-black text-emerald-600">Rp {{ number_format($totalSettled, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Dana 80% berhasil ditransfer ke rekening bank</span>
        </div>

        <!-- Card 2: Pending -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Menunggu Pencairan</span>
            <div class="text-3xl font-black text-amber-600">Rp {{ number_format($totalPending, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Akan diproses saat serah terima barang selesai</span>
        </div>

        <!-- Card 3: Model Komisi ADR-002 -->
        <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl p-6 text-white shadow-sm border border-indigo-950">
            <div class="flex items-center gap-2 mb-2 text-indigo-300 text-xs font-bold uppercase tracking-wider">
                <i class="bi bi-shield-check"></i> Skema Bagi Hasil (ADR-002)
            </div>
            <div class="text-xl font-black text-white">80% Mitra / 20% Platform</div>
            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                Platform memotong komisi 20% otomatis dari biaya penitipan. Biaya payment gateway ditanggung terpisah.
            </p>
        </div>
    </div>

    <!-- Bank Account Settings Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="bi bi-bank text-indigo-600"></i> Rekening Bank Tujuan Pencairan (Payout)
                </h3>
                <p class="text-xs text-slate-500">Hasil bagi hasil penitipan disalurkan otomatis via Xendit Disbursement ke rekening ini</p>
            </div>
        </div>

        <form action="{{ route('vendor.settlements.update_bank') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label for="bank_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Bank</label>
                <select id="bank_name" name="bank_name" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                    <option value="BCA" {{ ($vendor->bank_name ?? 'BCA') === 'BCA' ? 'selected' : '' }}>Bank Central Asia (BCA)</option>
                    <option value="MANDIRI" {{ ($vendor->bank_name ?? '') === 'MANDIRI' ? 'selected' : '' }}>Bank Mandiri</option>
                    <option value="BNI" {{ ($vendor->bank_name ?? '') === 'BNI' ? 'selected' : '' }}>Bank Negara Indonesia (BNI)</option>
                    <option value="BRI" {{ ($vendor->bank_name ?? '') === 'BRI' ? 'selected' : '' }}>Bank Rakyat Indonesia (BRI)</option>
                </select>
            </div>

            <div>
                <label for="bank_account_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nomor Rekening</label>
                <input id="bank_account_number" name="bank_account_number" type="text" required value="{{ old('bank_account_number', $vendor->bank_account_number ?? '1234567890') }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 font-mono">
            </div>

            <div>
                <label for="bank_account_holder" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Pemilik Rekening</label>
                <input id="bank_account_holder" name="bank_account_holder" type="text" required value="{{ old('bank_account_holder', $vendor->bank_account_holder ?? $vendor->business_name) }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                    Simpan Rekening
                </button>
            </div>
        </form>
    </div>

    <!-- Settlement History Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Riwayat Transaksi &amp; Pencairan Dana</h3>
                <p class="text-xs text-slate-500">Pencatatan mutasi kredit bagi hasil 80% per pesanan</p>
            </div>
            <span class="text-xs font-semibold text-slate-400">
                Total Catatan: {{ $settlements->total() }} Transaksi
            </span>
        </div>

        @if($settlements->isEmpty())
            <div class="text-center py-12">
                <i class="bi bi-wallet2 text-3xl text-slate-300 block mb-2"></i>
                <p class="text-xs text-slate-400">Belum ada catatan pencairan dana. Transaksi akan muncul setelah pesanan diselesaikan.</p>
            </div>
        @else
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">ID &amp; Tanggal</th>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Kode Booking</th>
                            <th class="py-3 px-4 sm:px-5">Pelanggan</th>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Nominal Bersih (80%)</th>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Ref Payout</th>
                            <th class="py-3 px-4 sm:px-5 whitespace-nowrap">Status Payout</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($settlements as $s)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-slate-900 block text-xs">SETTLE-{{ $s->id }}</span>
                                    <span class="text-[11px] text-slate-400 font-sans block">{{ $s->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 font-mono font-bold text-indigo-600 whitespace-nowrap">
                                    <a href="{{ route('vendor.bookings.show', $s->booking->booking_code) }}" class="hover:underline inline-flex items-center gap-1">
                                        {{ $s->booking->booking_code }}
                                    </a>
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 font-medium text-slate-900 truncate max-w-[150px]">
                                    {{ $s->booking->customer->name }}
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 font-bold text-emerald-600 text-sm whitespace-nowrap">
                                    Rp {{ number_format($s->amount, 0, ',', '.') }}
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    {{ $s->payout_reference ?? 'Belum Digenerate' }}
                                </td>

                                <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap shadow-sm {{ $s->status->badgeClasses() }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $s->status === \App\Enums\SettlementStatus::Settled ? 'bg-emerald-500' : ($s->status === \App\Enums\SettlementStatus::OnHold ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                        {{ $s->status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($settlements->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $settlements->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
