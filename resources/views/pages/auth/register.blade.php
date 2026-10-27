@extends('layouts.app')

@section('title', 'Daftar Akun Baru - TitipSini')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Buat Akun TitipSini</h2>
            <p class="mt-2 text-sm text-slate-600">
                Pilih jenis akun yang sesuai dengan kebutuhan Anda
            </p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Role Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Daftar Sebagai</label>
                    <div class="grid grid-cols-2 gap-3" x-data="{ role: 'customer' }">
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 hover:border-indigo-500 cursor-pointer transition">
                            <input type="radio" name="role" value="customer" checked class="text-indigo-600 focus:ring-indigo-500" onclick="document.getElementById('vendor-fields').classList.add('hidden')">
                            <span class="text-xs font-bold text-slate-800">Pelanggan</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 hover:border-indigo-500 cursor-pointer transition">
                            <input type="radio" name="role" value="vendor" class="text-indigo-600 focus:ring-indigo-500" onclick="document.getElementById('vendor-fields').classList.remove('hidden')">
                            <span class="text-xs font-bold text-indigo-900">Mitra Vendor</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm placeholder-slate-400"
                        placeholder="Budi Pratama">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm placeholder-slate-400"
                        placeholder="nama@email.com">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp / Telepon</label>
                    <input id="phone" name="phone" type="text" required value="{{ old('phone') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm placeholder-slate-400"
                        placeholder="081234567890">
                    @error('phone')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vendor Specific Fields -->
                <div id="vendor-fields" class="hidden space-y-4 pt-2 border-t border-slate-100">
                    <div>
                        <label for="business_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Usaha / Loker</label>
                        <input id="business_name" name="business_name" type="text" value="{{ old('business_name') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                            placeholder="Contoh: Loker Aman Gambir">
                    </div>

                    <div>
                        <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Tempat Penitipan</label>
                        <textarea id="address" name="address" rows="2"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                            placeholder="Jl. Stasiun Gambir No. 12, Jakarta">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                    <input id="password" name="password" type="password" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                        placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ulangi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                        placeholder="Konfirmasi kata sandi">
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-600/25 transition active:scale-[0.99]">
                    Daftar Akun Baru
                </button>
            </form>

            <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-100">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">
                    Masuk di sini
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
