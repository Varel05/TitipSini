@extends('layouts.admin')

@section('title', 'Permohonan Izin Akses SKCK - Superadmin')
@section('page_title', 'Tata Kelola Akses Dokumen Sensitif SKCK (Kebijakan 6.3)')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Superadmin Exclusive Notice -->
    <div class="bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white border border-indigo-900/30">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl bg-indigo-600/30 text-indigo-400 flex items-center justify-center text-lg">
                <i class="bi bi-shield-lock-fill"></i>
            </span>
            <div>
                <h2 class="text-lg font-bold text-white">Otorisasi Akses Dokumen SKCK Mitra</h2>
                <p class="text-xs text-slate-300">
                    Sesuai Kebijakan 6.3, dokumen SKCK fisik dilindungi enkripsi dan akses terbatas. Admin operasional wajib memperoleh persetujuan Superadmin sebelum dapat melihat berkas asli dengan jendela akses waktu sementara.
                </p>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if($requests->isEmpty())
            <div class="text-center py-16">
                <i class="bi bi-file-earmark-check text-3xl text-slate-300 mb-2 block"></i>
                <h4 class="font-bold text-slate-800 text-sm">Tidak ada permohonan akses SKCK</h4>
                <p class="text-xs text-slate-400 mt-1">Saat ini belum ada pengajuan akses dokumen dari admin operasional.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-6">Waktu Pengajuan</th>
                            <th class="py-3.5 px-6">Admin Pemohon</th>
                            <th class="py-3.5 px-6">Mitra Target</th>
                            <th class="py-3.5 px-6">Alasan Pengajuan</th>
                            <th class="py-3.5 px-6">Status &amp; Masa Izin</th>
                            <th class="py-3.5 px-6 text-right">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($requests as $req)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6 text-slate-500 font-mono">
                                    {{ $req->created_at->diffForHumans() }}
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 block">{{ $req->admin->name }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $req->admin->email }}</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 block">{{ $req->vendorProfile->business_name }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $req->vendorProfile->city }}</span>
                                </td>

                                <td class="py-4 px-6 max-w-xs">
                                    <p class="text-slate-800 line-clamp-2">{{ $req->reason }}</p>
                                </td>

                                <td class="py-4 px-6">
                                    @if($req->status === 'approved')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Disetujui s.d {{ $req->expires_at?->format('H:i') }}
                                        </span>
                                    @elseif($req->status === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu Review
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-6 text-right">
                                    @if($req->status === 'pending')
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('admin.skck_requests.reject', $req->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="rejection_reason" value="Alasan pengajuan belum cukup kuat untuk membuka berkas sensitif SKCK.">
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-white border border-rose-300 text-rose-600 hover:bg-rose-50 font-bold text-xs transition">
                                                    Tolak
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.skck_requests.approve', $req->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition">
                                                    Izinkan (2 Jam)
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Selesai ditinjau</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

