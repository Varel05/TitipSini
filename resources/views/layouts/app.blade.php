<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TitipSini — Platform Penitipan Barang & Koper Terpercaya')</title>
    <meta name="description" content="@yield('meta_description', 'Platform penitipan barang, koper, dan dokumen terpercaya di Indonesia. Bayar aman saat pengambilan, didukung asuransi dan pengawasan 24 jam.')">

    <!-- Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons for clean UI symbols -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-indigo-700 via-blue-700 to-indigo-800 text-white text-xs font-medium py-2 px-4 text-center tracking-wide">
        <span class="inline-flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Jaminan Keamanan: Barang dilindungi asuransi s.d Rp 10 Juta &amp; bayar fleksibel saat barang diambil.</span>
        </span>
    </div>

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-50 glassmorphism border-b border-slate-200/80 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                            <i class="bi bi-box-seam-fill text-xl"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors">
                                Titip<span class="text-indigo-600">Sini</span>
                            </span>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 -mt-1">Aman &amp; Terverifikasi</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900' }} transition-colors">
                        Beranda
                    </a>
                    <a href="{{ route('catalog.index') }}" class="text-sm font-semibold {{ request()->routeIs('catalog.*') ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900' }} transition-colors flex items-center gap-1.5">
                        <i class="bi bi-compass"></i> Cari Lokasi Titip
                    </a>
                    <a href="{{ route('faq') }}" class="text-sm font-semibold {{ request()->routeIs('faq') ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900' }} transition-colors">
                        Cara Kerja &amp; Bantuan
                    </a>
                    <a href="{{ route('booking.index') }}" class="text-sm font-semibold {{ request()->routeIs('booking.*') ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900' }} transition-colors flex items-center gap-1.5">
                        <i class="bi bi-ticket-perforated"></i> Pesanan Saya
                    </a>
                    <a href="{{ route('about') }}" class="text-sm font-semibold {{ request()->routeIs('about') ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900' }} transition-colors">
                        Tentang Kami
                    </a>
                </nav>

                <!-- Action CTA & Auth Buttons -->
                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->role->value === 'vendor')
                            <a href="{{ route('vendor.dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition">
                                <i class="bi bi-shop"></i> Portal Mitra
                            </a>
                        @endif

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="hidden sm:inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold text-slate-500 hover:text-rose-600 transition">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-slate-700 hover:text-indigo-600 px-3 py-2 transition">
                            Masuk
                        </a>
                    @endauth

                    <a href="{{ route('catalog.index') }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 transition-all hover:-translate-y-0.5">
                        <i class="bi bi-geo-alt-fill text-indigo-200"></i>
                        <span>Titip Sekarang</span>
                    </a>

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" type="button" class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" aria-label="Toggle navigation">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div id="mobile-menu" class="hidden md:hidden pb-6 pt-2 border-t border-slate-100">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50' }}">
                        Beranda
                    </a>
                    <a href="{{ route('catalog.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('catalog.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50' }}">
                        Cari Lokasi Titip
                    </a>
                    <a href="{{ route('faq') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('faq') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50' }}">
                        Cara Kerja &amp; Bantuan
                    </a>
                    <a href="{{ route('about') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('about') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50' }}">
                        Tentang Kami
                    </a>
                    <div class="pt-3 border-t border-slate-100">
                        <a href="{{ route('catalog.index') }}" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600">
                            <i class="bi bi-geo-alt-fill"></i> Mulai Penitipan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Dynamic Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="bg-slate-950 text-slate-400 pt-16 pb-12 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-slate-800">
                <!-- Col 1: About & Brand -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                            <i class="bi bi-box-seam-fill text-lg"></i>
                        </div>
                        <span class="text-2xl font-black text-white">Titip<span class="text-indigo-400">Sini</span></span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
                        Platform jaringan penitipan koper, barang berharga, dan dokumen nomor #1 di Indonesia. Mitra terverifikasi ketat, pengawasan CCTV 24 jam, dan bayar aman saat pengambilan.
                    </p>
                    <div class="flex items-center gap-4 pt-2 text-slate-400">
                        <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400 font-medium bg-emerald-950/60 border border-emerald-800/60 px-2.5 py-1 rounded-full">
                            <i class="bi bi-shield-check"></i> Mitra Terverifikasi KYC
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs text-blue-400 font-medium bg-blue-950/60 border border-blue-800/60 px-2.5 py-1 rounded-full">
                            <i class="bi bi-credit-card-2-front"></i> Settlement Xendit
                        </span>
                    </div>
                </div>

                <!-- Col 2: Navigasi Cepat -->
                <div>
                    <h3 class="text-white text-sm font-semibold tracking-wider uppercase mb-4">Layanan</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('catalog.index', ['category' => 'koper-tas-wisata']) }}" class="hover:text-white transition-colors">Titip Koper &amp; Tas</a></li>
                        <li><a href="{{ route('catalog.index', ['category' => 'dokumen-berkas-penting']) }}" class="hover:text-white transition-colors">SafeVault Dokumen</a></li>
                        <li><a href="{{ route('catalog.index', ['category' => 'elektronik-gadget']) }}" class="hover:text-white transition-colors">Titip Laptop &amp; Gadget</a></li>
                        <li><a href="{{ route('catalog.index', ['category' => 'helm-perlengkapan-rider']) }}" class="hover:text-white transition-colors">Loker Helm &amp; Motor</a></li>
                        <li><a href="{{ route('catalog.index') }}" class="hover:text-white transition-colors">Semua Lokasi Mitra</a></li>
                    </ul>
                </div>

                <!-- Col 3: Informasi & Bantuan -->
                <div>
                    <h3 class="text-white text-sm font-semibold tracking-wider uppercase mb-4">Informasi</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('faq') }}" class="hover:text-white transition-colors">Cara Kerja Sistem</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">Tentang TitipSini</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Syarat &amp; Ketentuan</a></li>
                        <li><a href="{{ route('terms') }}#kebijakan-privasi" class="hover:text-white transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="{{ route('faq') }}#asuransi" class="hover:text-white transition-colors">Klaim Asuransi Barang</a></li>
                    </ul>
                </div>

                <!-- Col 4: Keamanan & Kontak -->
                <div>
                    <h3 class="text-white text-sm font-semibold tracking-wider uppercase mb-4">Kontak Layanan</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li class="flex items-center gap-2">
                            <i class="bi bi-envelope text-indigo-400"></i>
                            <span>bantuan@titipsini.com</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-clock-history text-indigo-400"></i>
                            <span>Layanan 24 Jam Nonstop</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-geo-alt text-indigo-400"></i>
                            <span>Jakarta Pusat, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright & Security Notice -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} TitipSini Indonesia. Hak cipta dilindungi undang-undang.</p>
                <div class="flex items-center gap-6">
                    <span class="flex items-center gap-1.5"><i class="bi bi-lock-fill text-emerald-400"></i> Enkripsi Data 256-bit</span>
                    <span class="flex items-center gap-1.5"><i class="bi bi-patch-check-fill text-blue-400"></i> Verified Partner Gateway</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Drawer Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', () => {
                    menu.classList.toggle('hidden');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
