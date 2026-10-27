@extends('layouts.admin')

@section('title', 'Jejak Audit Sistem - Admin')
@section('page_title', 'Jejak Audit Sistem (Security & Audit Trail)')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Filters & Search -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Action Filter Dropdown -->
            <form action="{{ route('admin.audit_logs.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <select name="action" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-purple-500">
                    <option value="all">Semua Tipe Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ $currentAction === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>

                <div class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari IP, model, aktor..."
                        class="pl-9 pr-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 text-xs placeholder-slate-400">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>

                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    Filter
                </button>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if($auditLogs->isEmpty())
            <div class="text-center py-16">
                <i class="bi bi-journal-x text-3xl text-slate-300 mb-2 block"></i>
                <h4 class="font-bold text-slate-800 text-sm">Tidak ada log audit ditemukan</h4>
                <p class="text-xs text-slate-400 mt-1">Coba gunakan filter lain atau reset parameter pencarian.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-6">Timestamp &amp; ID</th>
                            <th class="py-3.5 px-6">Aktor / User</th>
                            <th class="py-3.5 px-6">Aksi (Event)</th>
                            <th class="py-3.5 px-6">Target Entitas</th>
                            <th class="py-3.5 px-6">IP &amp; Device</th>
                            <th class="py-3.5 px-6">Detail Metadata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-mono">
                        @foreach($auditLogs as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 block">#LOG-{{ $log->id }}</span>
                                    <span class="text-[11px] text-slate-400 font-sans">{{ $log->created_at->translatedFormat('d M Y, H:i:s') }}</span>
                                </td>

                                <td class="py-4 px-6 font-sans">
                                    <span class="font-semibold text-slate-900 block">{{ $log->user->name ?? 'System Gateway' }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $log->user ? $log->user->role->label() : 'Automated' }}</span>
                                </td>

                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-slate-600">
                                    <span class="font-bold block">{{ class_basename($log->auditable_type ?? '') }}</span>
                                    <span class="text-[11px] text-slate-400">ID: {{ $log->auditable_id }}</span>
                                </td>

                                <td class="py-4 px-6 text-[11px] text-slate-500">
                                    <span class="font-bold text-slate-700 block">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                </td>

                                <td class="py-4 px-6 font-sans">
                                    @if($log->new_values)
                                        <details class="cursor-pointer">
                                            <summary class="text-xs font-semibold text-purple-600 hover:text-purple-700">Lihat Data (JSON)</summary>
                                            <pre class="mt-2 p-2 bg-slate-900 text-slate-200 text-[10px] rounded-lg overflow-x-auto max-w-xs font-mono">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @else
                                        <span class="text-slate-400 text-xs italic">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
