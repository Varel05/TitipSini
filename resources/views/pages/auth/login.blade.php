@extends('layouts.app')

@section('title', 'Masuk ke Akun - TitipSini')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white mb-3 shadow-lg shadow-indigo-600/30">
                <i class="bi bi-person-fill-lock text-2xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke Akun</h2>
            <p class="mt-2 text-sm text-slate-600">
                Kelola pesanan penitipan atau operasional mitra vendor Anda
            </p>
        </div>

        <!-- Quick Demo Role Switcher -->
        <div class="bg-indigo-50/80 rounded-2xl p-4 border border-indigo-100">
            <div class="flex items-center gap-2 mb-2 text-indigo-950 font-bold text-xs uppercase tracking-wider">
                <i class="bi bi-lightning-charge-fill text-amber-500"></i> Login Instan (Demo Mode)
            </div>
            <p class="text-xs text-indigo-800 mb-3">Klik tombol untuk langsung masuk tanpa mengetik kredensial:</p>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('auth.quick_login', 'vendor') }}" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-indigo-200 text-xs font-bold text-indigo-900 shadow-sm flex items-center gap-1.5 transition">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Mitra Vendor
                </a>
                <a href="{{ route('auth.quick_login', 'customer') }}" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-indigo-200 text-xs font-bold text-slate-700 shadow-sm flex items-center gap-1.5 transition">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Pelanggan
                </a>
                <a href="{{ route('auth.quick_login', 'superadmin') }}" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-indigo-200 text-xs font-bold text-purple-900 shadow-sm flex items-center gap-1.5 transition">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Superadmin
                </a>
                <a href="{{ route('auth.quick_login', 'admin') }}" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-indigo-200 text-xs font-bold text-indigo-800 shadow-sm flex items-center gap-1.5 transition">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Admin Ops
                </a>
            </div>
        </div>

        <!-- Standard Login Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm placeholder-slate-400 @error('email') border-rose-500 @enderror"
                        placeholder="nama@email.com">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kata Sandi</label>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm placeholder-slate-400"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded text-indigo-600 focus:ring-indigo-500 mr-2">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-600/25 transition active:scale-[0.99]">
                    Masuk Sekarang
                </button>
            </form>

            <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-100">
                Belum memiliki akun?
                <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700">
                    Daftar Akun Baru
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
