@extends('layouts.admin')

@section('title', 'Executive Dashboard - Admin')
@section('page_title', 'Ringkasan Platform & Monitoring Eksekutif')

@section('content')
<div class="space-y-8 max-w-7xl">

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-purple-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg border border-purple-900/30">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs uppercase font-bold text-purple-400 tracking-wider">Pusat Kendali Eksekutif</span>
                    @if($isSuperadmin)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            <i class="bi bi-globe2"></i> Cakupan Nasional
                        </span>
                    @elseif($assignedRegion)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            <i class="bi bi-geo-alt-fill"></i> Wilayah: {{ $assignedRegion->name }} ({{ $assignedRegion->code }})
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                            <i class="bi bi-exclamation-octagon"></i> Belum Memiliki Wilayah Penugasan
                        </span>
                    @endif
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Platform Marketplace TitipSini</h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                    @if($isSuperadmin)
                        Monitoring operasional real-time seluruh Indonesia, rekonsiliasi finansial bagi hasil 20/80, verifikasi KYC mitra, dan tata kelola pengawasan sistem.
                    @else
                        Monitoring operasional regional, verifikasi KYC mitra lokal, moderasi listing di wilayah {{ $assignedRegion->name ?? 'operasional' }}.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.vendors.index', ['status' => 'pending']) }}" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/30 transition flex items-center gap-2">
                    <i class="bi bi-shield-check"></i> Antrian KYC ({{ $pendingKycCount }})
                </a>
                @if($isSuperadmin)
                    <a href="{{ route('admin.finance.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs transition">
                        Rekonsiliasi Finansial &rarr;
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- 4 Financial Metrics (ADR-002: 20% Platform / 80% Vendor) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- GMV -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Total Transaksi (GMV)</span>
            <div class="text-3xl font-black text-slate-900">Rp {{ number_format($totalGmv, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">{{ $completedBookingsCount }} Pesanan Berhasil Ditutup</span>
        </div>

        <!-- 20% Platform Commission -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Pendapatan Platform (20%)</span>
            <div class="text-3xl font-black text-purple-600">Rp {{ number_format($totalPlatformRevenue, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Komisi bersih TitipSini</span>
        </div>

        <!-- 80% Vendor Payout Disbursed -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Pencairan Mitra (80%)</span>
            <div class="text-3xl font-black text-emerald-600">Rp {{ number_format($totalVendorDisbursed, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Disalurkan via Xendit Payout</span>
        </div>

        <!-- Pending Vendor Payout -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Pending Payout Mitra</span>
            <div class="text-3xl font-black text-amber-600">Rp {{ number_format($pendingVendorPayout, 0, ',', '.') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Menunggu penyelesaian serah-terima</span>
        </div>
    </div>

    <!-- Operational Queues Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.vendors.index', ['status' => 'pending']) }}" class="p-5 rounded-2xl bg-white border border-slate-200/80 hover:border-purple-500 shadow-sm transition block group">
            <div class="flex items-center justify-between text-slate-600 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Antrian Verifikasi KYC</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="bi bi-clock-history"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 group-hover:text-purple-600 transition">{{ $pendingKycCount }} Mitra</div>
            <span class="text-xs text-slate-400 mt-1 block">Menunggu verifikasi identitas & SKCK &rarr;</span>
        </a>

        <a href="{{ route('admin.listings.index', ['status' => 'submitted']) }}" class="p-5 rounded-2xl bg-white border border-slate-200/80 hover:border-purple-500 shadow-sm transition block group">
            <div class="flex items-center justify-between text-slate-600 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Moderasi Listing Baru</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="bi bi-shop"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 group-hover:text-purple-600 transition">{{ $pendingListingCount }} Tempat</div>
            <span class="text-xs text-slate-400 mt-1 block">Menunggu persetujuan tayang publik &rarr;</span>
        </a>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between text-slate-600 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Booking Aktif Berjalan</span>
                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="bi bi-box-seam"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $activeBookingsCount }} Pesanan</div>
            <span class="text-xs text-slate-400 mt-1 block">Barang sedang tersimpan / siap diambil</span>
        </div>
    </div>

    <!-- Urgent KYC Review Queue Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Permohonan Verifikasi KYC Mitra Terbaru</h3>
                <p class="text-xs text-slate-500">Pemeriksaan profil vendor individu dan kelayakan operasional</p>
            </div>
            <a href="{{ route('admin.vendors.index') }}" class="text-xs font-bold text-purple-600 hover:text-purple-700">
                Kelola Semua Mitra &rarr;
            </a>
        </div>

        @if($urgentKycVendors->isEmpty())
            <div class="text-center py-10">
                <i class="bi bi-check-circle text-emerald-500 text-2xl mb-2 block"></i>
                <p class="text-xs text-slate-500">Semua pendaftaran mitra telah diproses. Tidak ada permohonan tertunda.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($urgentKycVendors as $v)
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900">{{ $v->business_name }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $v->verification_status->badgeClasses() }}">
                                    {{ $v->verification_status->label() }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">
                                Pemilik: <strong>{{ $v->user->name }}</strong> ({{ $v->user->phone }}) • Alamat: {{ $v->address }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="{{ route('admin.vendors.show', $v->id) }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition">
                                Review Dokumen KYC &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($isSuperadmin)
    <!-- Recent System Audit Trail (Superadmin Exclusive) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Jejak Audit Sistem Terkini (Security &amp; Audit Trail)</h3>
                <p class="text-xs text-slate-500">Pencatatan mutasi transaksi finansial, check-in, dan aksi administratif</p>
            </div>
            <a href="{{ route('admin.audit_logs.index') }}" class="text-xs font-bold text-purple-600 hover:text-purple-700">
                Lihat Seluruh Log &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Aktor / User</th>
                        <th class="py-3 px-4">Aksi / Event</th>
                        <th class="py-3 px-4">Target Entitas</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-mono">
                    @forelse($recentAuditLogs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 text-slate-400 font-sans">{{ $log->created_at->diffForHumans() }}</td>
                            <td class="py-3 px-4 font-sans font-semibold text-slate-900">{{ $log->user->name ?? 'System' }}</td>
                            <td class="py-3 px-4 font-bold text-purple-700">{{ $log->action }}</td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">{{ class_basename($log->auditable_type ?? '') }} #{{ $log->auditable_id }}</td>
                            <td class="py-3 px-4 text-slate-400 text-[11px]">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 font-sans">Belum ada catatan audit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
