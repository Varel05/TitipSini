@extends('layouts.admin')

@section('title', 'Dossier KYC Mitra - ' . $vendor->business_name)
@section('page_title', 'Dossier Verifikasi KYC: ' . $vendor->business_name)

@section('content')
<div class="max-w-4xl space-y-6">

    <div>
        <a href="{{ route('admin.vendors.index') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-purple-600 transition">
            <i class="bi bi-arrow-left mr-1.5"></i> Kembali ke Daftar Verifikasi Mitra
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 sm:p-8 bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-bold text-purple-400 tracking-wider">Profil Mitra Vendor Individu</span>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-2xl font-extrabold text-white">{{ $vendor->business_name }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $vendor->verification_status->badgeClasses() }}">
                        {{ $vendor->verification_status->label() }}
                    </span>
                </div>
            </div>

            <div class="text-left sm:text-right text-xs text-slate-400">
                <span>Terdaftar sejak: {{ $vendor->created_at->translatedFormat('d F Y') }}</span>
                @if($vendor->verified_at)
                    <span class="block text-emerald-400 font-semibold mt-0.5">Diverifikasi: {{ $vendor->verified_at->translatedFormat('d M Y, H:i') }}</span>
                @endif
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-8">
            
            <!-- Vendor Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm">
                <!-- Contact & Masked Identity (Privacy Policy 6.3) -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="bi bi-person-lines-fill text-purple-600"></i> Data Pemilik Akun
                        </span>
                        <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            <i class="bi bi-shield-lock-fill"></i> NIK Dimasking
                        </span>
                    </h3>
                    <div class="space-y-2 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nama Lengkap:</span>
                            <span class="font-bold text-slate-900">{{ $vendor->user->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">NIK / Nomor KTP:</span>
                            <span class="font-mono font-bold text-slate-800 tracking-wider">
                                {{ \App\Models\VendorProfile::maskIdentity($vendor->user->customerProfile?->identity_number ?? '3171012345670001') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Email:</span>
                            <span class="font-medium text-slate-800">{{ $vendor->user->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nomor Telepon:</span>
                            <span class="font-bold text-slate-900 font-mono">{{ $vendor->user->phone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status Toko:</span>
                            <span class="font-bold {{ $vendor->is_open ? 'text-emerald-600' : 'text-slate-500' }}">
                                {{ $vendor->is_open ? 'BUKA (Aktif Menerima)' : 'TUTUP' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Bank Details -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center gap-1.5">
                        <i class="bi bi-bank text-purple-600"></i> Rekening Bank Penyaluran Payout (80%)
                    </h3>
                    <div class="space-y-2 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Bank Tujuan:</span>
                            <span class="font-bold text-slate-900">{{ $vendor->bank_name ?? 'BCA' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nomor Rekening:</span>
                            <span class="font-mono font-bold text-slate-900">{{ $vendor->bank_account_number ?? '1234567890' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nama Pemilik:</span>
                            <span class="font-medium text-slate-900">{{ $vendor->bank_account_holder ?? $vendor->business_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Xendit Sub-Account:</span>
                            <span class="font-mono text-[11px] text-slate-500">{{ $vendor->submerchant_id ?? 'Auto Managed via xenPlatform' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address and Physical Security -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs sm:text-sm">
                <span class="text-xs uppercase font-bold text-slate-500 block">Alamat Fisik Lokasi Penitipan</span>
                <p class="font-medium text-slate-800">{{ $vendor->address }} ({{ $vendor->city ?? 'Jakarta' }})</p>
            </div>

            <!-- SKCK Governance & Lifecycle Management Card (Policies 6.2 & 6.3) -->
            <div class="p-6 rounded-3xl bg-slate-900 text-white space-y-4 border border-slate-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                    <div>
                        <h4 class="font-bold text-sm text-white flex items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-amber-400"></i>
                            Tata Kelola Dokumen SKCK (Kebijakan 6.2 &amp; 6.3)
                        </h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Masa berlaku, grace period 30 hari, izin akses berbatas waktu, dan retensi 7 hari pasca-verifikasi.
                        </p>
                    </div>

                    <div>
                        @if($vendor->isSkckBlocked())
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                <i class="bi bi-x-circle-fill"></i> Grace Period Habis - Booking Diblokir
                            </span>
                        @elseif($vendor->isInSkckGracePeriod())
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                <i class="bi bi-exclamation-triangle-fill"></i> Masa Tenggang 30 Hari Aktif
                            </span>
                        @elseif($vendor->skck_pruned_at)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-700 text-slate-300 border border-slate-600">
                                <i class="bi bi-trash3-fill"></i> Dokumen Fisik Telah Diprune
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <i class="bi bi-check-circle-fill"></i> Status SKCK Terdaftar
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Masa Berlaku Kedaluwarsa</span>
                        <span class="font-bold text-white text-sm">
                            {{ $vendor->skck_expires_at ? $vendor->skck_expires_at->format('d M Y') : 'Masa aktif normal' }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Batas Akhir Masa Tenggang</span>
                        <span class="font-bold text-white text-sm">
                            {{ $vendor->skck_grace_period_ends_at ? $vendor->skck_grace_period_ends_at->format('d M Y') : ($vendor->skck_expires_at ? $vendor->skck_expires_at->copy()->addDays(30)->format('d M Y') : '-') }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Otorisasi Akses Dokumen Anda</span>
                        @if(auth()->user()->isSuperadmin())
                            <span class="font-bold text-indigo-300 text-sm">Superadmin (Akses Penuh)</span>
                        @elseif($vendor->hasActiveSkckAccessFor(auth()->user()))
                            <span class="font-bold text-emerald-400 text-sm">Diizinkan (Signed Window)</span>
                        @else
                            <span class="font-bold text-amber-400 text-sm">Perlu Izin Superadmin</span>
                        @endif
                    </div>
                </div>

                <!-- Document Access Action Buttons -->
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    @if(auth()->user()->isSuperadmin() || $vendor->hasActiveSkckAccessFor(auth()->user()))
                        @if($vendor->skck_pruned_at)
                            <span class="text-xs text-slate-400 italic">
                                <i class="bi bi-info-circle"></i> Berkas fisik SKCK telah dibersihkan secara otomatis sesuai kebijakan retensi privasi maksimal 7 hari pasca-verifikasi.
                            </span>
                        @else
                            <a href="{{ route('admin.vendors.download_skck', $vendor->id) }}" target="_blank"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Buka / Unduh Dokumen SKCK Asli
                            </a>
                        @endif
                    @else
                        <!-- Form Request Access to Superadmin -->
                        <form action="{{ route('admin.vendors.request_skck', $vendor->id) }}" method="POST" class="flex items-center gap-2 w-full sm:w-auto">
                            @csrf
                            <input type="text" name="reason" placeholder="Alasan kebutuhan melihat SKCK fisik..." required
                                class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 w-full sm:w-80">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition whitespace-nowrap">
                                <i class="bi bi-key-fill"></i> Ajukan Izin Akses
                            </button>
                        </form>
                    @endif

                    <!-- Privacy Anonymize Button -->
                    @if(!$vendor->skck_pruned_at && $vendor->isVerified())
                        <form action="{{ route('admin.vendors.privacy_anonymize', $vendor->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memproses Privacy Request ini? Berkas KTP dan SKCK fisik akan dibersihkan permanen.');" class="ml-auto">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-semibold transition flex items-center gap-1.5">
                                <i class="bi bi-shield-x"></i> Bersihkan Dokumen (Privacy Request)
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if($vendor->revision_notes)
                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-xs text-sky-900">
                    <strong>Catatan Permintaan Revisi:</strong>
                    <p class="mt-1 font-medium">{{ $vendor->revision_notes }}</p>
                </div>
            @endif

            @if($vendor->rejection_reason)
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                    <strong>Catatan Penolakan Sebelumnya:</strong>
                    <p class="mt-1 font-medium">{{ $vendor->rejection_reason }}</p>
                </div>
            @endif

            <!-- KYC Decision Actions (Policy 6.1: Approve, Needs Revision, Reject) -->
            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Seluruh keputusan administratif dicatat ke jejak audit sistem.
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <!-- Revision Form (Policy 6.1 Poin 3) -->
                    <form action="{{ route('admin.vendors.request_revision', $vendor->id) }}" method="POST" class="inline w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="revision_notes" value="Dokumen identitas atau SKCK kurang jelas/perlu perbaikan. Silakan unggah ulang.">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white border border-sky-300 hover:bg-sky-50 text-sky-700 font-bold text-xs transition">
                            <i class="bi bi-arrow-repeat"></i> Minta Revisi Berkas
                        </button>
                    </form>

                    <!-- Reject Form -->
                    <form action="{{ route('admin.vendors.reject', $vendor->id) }}" method="POST" class="inline w-full sm:w-auto" onsubmit="return confirm('Apakah Anda yakin ingin menolak permohonan KYC mitra ini?');">
                        @csrf
                        <input type="hidden" name="rejection_reason" value="Dokumen identitas atau kelayakan operasional tidak memenuhi syarat verifikasi TitipSini.">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white border border-rose-300 hover:bg-rose-50 text-rose-700 font-bold text-xs transition">
                            Tolak Verifikasi
                        </button>
                    </form>

                    <!-- Approve Form -->
                    <form action="{{ route('admin.vendors.approve', $vendor->id) }}" method="POST" class="inline w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/25 transition flex items-center justify-center gap-1.5">
                            <i class="bi bi-check-circle-fill"></i> Setujui &amp; Verifikasi Mitra
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
