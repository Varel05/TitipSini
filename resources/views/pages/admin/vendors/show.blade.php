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
                <!-- Contact & User Info -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center gap-1.5">
                        <i class="bi bi-person-lines-fill text-purple-600"></i> Data Pemilik Akun
                    </h3>
                    <div class="space-y-2 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nama Lengkap:</span>
                            <span class="font-bold text-slate-900">{{ $vendor->user->name }}</span>
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

            <!-- ADR-002 Document Governance & SKCK Policy Notice -->
            <div class="p-6 rounded-3xl bg-indigo-50/60 border border-indigo-200 space-y-3">
                <div class="flex items-center gap-2 text-indigo-950 font-bold text-sm">
                    <i class="bi bi-shield-check text-indigo-600 text-lg"></i>
                    Tata Kelola Dokumen Identitas &amp; SKCK (ADR-002 section 5 &amp; 11)
                </div>
                <p class="text-xs text-indigo-900 leading-relaxed">
                    Dokumen identitas (KTP) dan Surat Keterangan Catatan Kepolisian (SKCK) merupakan data berisiko tinggi. Dokumen disimpan terenkripsi di storage privat. Selaku Administrator, pastikan data yang ditinjau telah memenuhi kriteria bebas catatan kriminal dan validitas alamat operasional sebelum memberikan status terverifikasi.
                </p>
                <div class="flex items-center gap-4 text-xs font-semibold text-indigo-700 pt-1">
                    <span class="flex items-center gap-1"><i class="bi bi-file-earmark-lock"></i> KTP: Tersimpan Aman</span>
                    <span class="flex items-center gap-1"><i class="bi bi-file-earmark-check"></i> SKCK: Terlampir</span>
                </div>
            </div>

            @if($vendor->rejection_reason)
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                    <strong>Catatan Penolakan Sebelumnya:</strong>
                    <p class="mt-1 font-medium">{{ $vendor->rejection_reason }}</p>
                </div>
            @endif

            <!-- KYC Decision Actions -->
            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Aksi ini akan dicatat ke dalam audit log sistem dengan identitas akun Anda.
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <!-- Reject Form -->
                    <form action="{{ route('admin.vendors.reject', $vendor->id) }}" method="POST" class="inline w-full sm:w-auto" onsubmit="return confirm('Apakah Anda yakin ingin menolak permohonan KYC mitra ini?');">
                        @csrf
                        <input type="hidden" name="rejection_reason" value="Dokumen identitas atau kelayakan operasional belum memenuhi standar TitipSini.">
                        <button type="submit" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-white border border-rose-300 hover:bg-rose-50 text-rose-700 font-bold text-xs transition">
                            Tolak Verifikasi
                        </button>
                    </form>

                    <!-- Approve Form -->
                    <form action="{{ route('admin.vendors.approve', $vendor->id) }}" method="POST" class="inline w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/25 transition flex items-center justify-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> Setujui &amp; Verifikasi Mitra
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
