@extends('layouts.admin')

@section('title', 'Berkas Sengketa #DSP-' . $dispute->id . ' - Admin')
@section('page_title', 'Investigasi Sengketa #DSP-' . $dispute->id)

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Top Navigation / Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.disputes.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-purple-600 transition">
            <i class="bi bi-arrow-left"></i> Kembali ke Pusat Sengketa
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $dispute->status->badgeClasses() }}">
                Status Kasus: {{ $dispute->status->label() }}
            </span>
            @if($dispute->is_escalated)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                    <i class="bi bi-shield-lock-fill"></i> Eskalasi Superadmin
                </span>
            @endif
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-amber-600 text-sm"></i>
            {{ session('warning') }}
        </div>
    @endif

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Dispute Details & Case Evidence -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card: Dispute Statement -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $dispute->category->badgeClasses() }}">
                            {{ $dispute->category->label() }}
                        </span>
                        <h2 class="text-base font-black text-slate-900 mt-2">{{ $dispute->title }}</h2>
                    </div>
                    <span class="text-xs text-slate-400">
                        Diajukan: {{ $dispute->created_at->format('d M Y, H:i') }}
                    </span>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Kronologi & Pernyataan Pelanggan:</span>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-xs leading-relaxed whitespace-pre-line">
                        {{ $dispute->description }}
                    </div>
                </div>

                <!-- Parties Involved -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="font-bold text-slate-400 uppercase text-[10px] block">Pelapor (Customer)</span>
                        <strong class="text-slate-900 block">{{ $dispute->customer->name }}</strong>
                        <span class="text-slate-600 block">{{ $dispute->customer->email }}</span>
                        <span class="text-purple-700 font-mono">{{ $dispute->customer->phone ?? '-' }}</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="font-bold text-slate-400 uppercase text-[10px] block">Mitra Terlapor</span>
                        <strong class="text-slate-900 block">{{ $dispute->vendorProfile->business_name }}</strong>
                        <span class="text-slate-600 block">{{ $booking->listing->title }} • {{ $booking->listing->city }}</span>
                        <span class="text-purple-700 font-mono">{{ $dispute->vendorProfile->phone ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card: Booking Financial & Escrow Hold Telemetry -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-sm">Status Penahanan Dana Mitra (Escrow Hold - Policy 11.1)</h3>
                
                @php
                    $settlement = $booking->vendorSettlement;
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Total Transaksi Customer</span>
                        <strong class="text-slate-900 font-black text-sm">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</strong>
                    </div>

                    <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200">
                        <span class="text-purple-700 block text-[10px]">Porsi Net Mitra (80%)</span>
                        <strong class="text-purple-900 font-black text-sm">Rp {{ number_format($booking->vendor_earnings, 0, ',', '.') }}</strong>
                    </div>

                    <div class="p-4 rounded-2xl {{ $settlement?->status->value === 'on_hold' ? 'bg-rose-50 border border-rose-200' : 'bg-emerald-50 border border-emerald-200' }}">
                        <span class="block text-[10px] {{ $settlement?->status->value === 'on_hold' ? 'text-rose-700' : 'text-emerald-700' }}">Status Payout Mitra</span>
                        <strong class="block text-xs font-bold {{ $settlement?->status->value === 'on_hold' ? 'text-rose-800' : 'text-emerald-800' }}">
                            {{ $settlement?->status->label() ?? 'Belum Dicatat' }}
                        </strong>
                    </div>
                </div>

                @if($settlement?->status->value === 'on_hold')
                    <div class="p-3 rounded-xl bg-rose-100/70 border border-rose-200 text-rose-900 text-xs flex items-center gap-2">
                        <i class="bi bi-lock-fill text-rose-600"></i>
                        <span>Dana payout mitra sebesar <strong>Rp {{ number_format($settlement->amount, 0, ',', '.') }}</strong> sedang <strong>terkunci (Hold)</strong> di platform hingga sengketa ini diputuskan.</span>
                    </div>
                @endif
            </div>

            <!-- Card: Resolution Notes (if closed) -->
            @if($dispute->isClosed())
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h4 class="font-bold text-slate-900 text-sm">Putusan & Kesimpulan Mediasi</h4>
                        <span class="text-xs text-slate-400">{{ $dispute->resolved_at?->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 leading-relaxed whitespace-pre-line">
                        {{ $dispute->resolution_notes }}
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs pt-2">
                        <span>Pemutus: <strong class="text-slate-800">{{ $dispute->resolvedBy?->name ?? 'Administrator' }}</strong></span>
                        @if($dispute->compensation_amount > 0)
                            <span class="text-emerald-700 font-bold">Kompensasi Pelanggan: Rp {{ number_format($dispute->compensation_amount, 0, ',', '.') }}</span>
                        @endif
                        @if($dispute->vendor_penalty_amount > 0)
                            <span class="text-rose-700 font-bold">Denda/Beban Mitra: Rp {{ number_format($dispute->vendor_penalty_amount, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- Column 3: Action Controls -->
        <div class="space-y-6">

            <!-- Ordinary Dispute Resolution (Admin Wilayah) -->
            @if(! $dispute->is_escalated && ! $dispute->isClosed())
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">
                            <i class="bi bi-hammer"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">Penyelesaian Admin Wilayah</h4>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">Policy 11.2 Operasional</span>
                        </div>
                    </div>

                    <!-- Resolve Form -->
                    <form action="{{ route('admin.disputes.resolve', $dispute->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Catatan Kesepakatan / Solusi <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="resolution_notes" rows="3" required minlength="5" maxlength="1000"
                                placeholder="Jelaskan solusi yang disepakati (misal: barang diserahkan kembali, vendor memberi ganti rugi fisik)..."
                                class="w-full p-2.5 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Nominal Pengurangan Payout Mitra (Rp, jika ada denda/potongan)
                            </label>
                            <input type="number" name="vendor_penalty_amount" value="0" min="0" max="{{ $booking->vendor_earnings }}"
                                class="w-full p-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-purple-600">
                        </div>

                        <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                            <i class="bi bi-check2-circle"></i> Selesaikan Sengketa & Rilis Dana
                        </button>
                    </form>

                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <!-- Reject Claim Form -->
                        <form action="{{ route('admin.disputes.reject', $dispute->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin klaim pengaduan ini tidak berdasar?');">
                            @csrf
                            <input type="hidden" name="rejection_notes" value="Klaim sengketa ditolak setelah pemeriksaan fisik segel keamanan terbukti utuh dan tidak ada pelanggaran mitra.">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                Tolak Klaim Sengketa (Lepaskan Payout)
                            </button>
                        </form>

                        <!-- Escalate to Superadmin -->
                        <form action="{{ route('admin.disputes.escalate', $dispute->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="escalation_reason" value="Terjadi kebuntuan mediasi / indikasi kerugian finansial berat yang melampaui wewenang wilayah.">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs transition border border-purple-200 flex items-center justify-center gap-1.5">
                                <i class="bi bi-arrow-up-right"></i> Eskalasikan ke Superadmin
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Superadmin Arbitration Module (Exclusive for Escalated Cases) -->
            @if($dispute->is_escalated && ! $dispute->isClosed())
                @if($isSuperadmin)
                    <div class="bg-white rounded-3xl border border-purple-200 shadow-sm p-6 space-y-5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-shield-fill-check"></i>
                            </span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Otoritas Arbitrase Superadmin</h4>
                                <span class="text-[10px] text-purple-700 font-semibold uppercase">Policy 11.1 & 11.2 Final</span>
                            </div>
                        </div>

                        <form action="{{ route('admin.disputes.arbitrate', $dispute->id) }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Putusan Arbitrase</label>
                                <select name="arbitration_decision" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs font-semibold">
                                    <option value="resolved">Menangkan / Selesaikan Sengketa (Resolved)</option>
                                    <option value="rejected">Tolak Pengaduan Pelanggan (Rejected)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Pertimbangan & Putusan Hukum Platform <span class="text-rose-500">*</span></label>
                                <textarea name="resolution_notes" rows="3" required minlength="5" maxlength="1000"
                                    placeholder="Jelaskan alasan putusan finansial, status barang, dan bukti investigasi..."
                                    class="w-full p-2.5 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-purple-600"></textarea>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Denda / Beban Finansial Mitra (Rp)</label>
                                <input type="number" name="vendor_penalty_amount" value="0" min="0" max="{{ $booking->vendor_earnings }}"
                                    class="w-full p-2 rounded-xl border border-slate-300 text-xs">
                            </div>

                            <!-- Vendor Account Freeze Toggle (Policy 7.3) -->
                            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="suspend_vendor" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                    <span class="text-xs font-bold text-rose-900">Bekukan Akun Mitra (Suspend Account)</span>
                                </label>
                                <p class="text-[10px] text-rose-700 leading-relaxed">
                                    Jika dicentang, mitra akan langsung dibekukan dan dilarang menerima pesanan baru di seluruh platform.
                                </p>
                                <input type="text" name="suspension_reason" placeholder="Alasan pembekuan (wajib jika dicentang)"
                                    class="w-full p-2 rounded-xl border border-rose-300 text-xs">
                            </div>

                            <button type="submit" class="w-full py-3.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs shadow-md shadow-purple-700/20 transition flex items-center justify-center gap-2">
                                <i class="bi bi-shield-check"></i> Eksekusi Putusan Arbitrase Final
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-purple-50 rounded-3xl border border-purple-200 p-6 space-y-3 text-purple-950 text-xs">
                        <strong class="font-bold flex items-center gap-1.5 text-sm">
                            <i class="bi bi-shield-lock-fill text-purple-700"></i> Kasus Dalam Penanganan Superadmin
                        </strong>
                        <p class="leading-relaxed">
                            Berdasarkan Policy 11.1, kategori sengketa ini tergolong berisiko tinggi (kehilangan barang / dugaan manipulasi / ancaman) dan penanganan telah dialihkan secara eksklusif kepada Superadmin.
                        </p>
                    </div>
                @endif
            @endif

            <!-- Vendor Status Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 space-y-2 text-xs">
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Status Mitra Saat Ini</span>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">{{ $dispute->vendorProfile->business_name }}</span>
                    @if($dispute->vendorProfile->is_suspended)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                            TERBEKUKAN (SUSPENDED)
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            Aktif
                        </span>
                    @endif
                </div>
                @if($dispute->vendorProfile->is_suspended)
                    <p class="text-[11px] text-rose-700 italic mt-1">
                        "{{ $dispute->vendorProfile->suspension_reason }}"
                    </p>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
