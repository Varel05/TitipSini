<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Vendor Portal') - TitipSini</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex text-slate-800 antialiased font-sans">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col justify-between hidden md:flex border-r border-slate-800">
        <div>
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800 gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-600/30">
                    <i class="bi bi-box-seam-fill text-lg"></i>
                </div>
                <div>
                    <span class="font-extrabold text-white text-lg tracking-tight">Titip<span class="text-indigo-400">Sini</span></span>
                    <span class="block text-[10px] text-slate-400 font-mono tracking-wider uppercase">Portal Mitra</span>
                </div>
            </div>

            <!-- Nav Links -->
            <div class="px-4 py-6 space-y-1 text-sm font-medium">
                <a href="{{ route('vendor.dashboard') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('vendor.dashboard') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <i class="bi bi-grid-1x2-fill text-base"></i>
                    Dashboard
                </a>

                <a href="{{ route('vendor.bookings.index') }}"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('vendor.bookings.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-ticket-perforated-fill text-base"></i>
                        <span>Operasional Penitipan</span>
                    </div>
                </a>

                <a href="{{ route('vendor.listings.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('vendor.listings.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <i class="bi bi-shop text-base"></i>
                    Kelola Listing & Tarif
                </a>

                <a href="{{ route('vendor.settlements.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('vendor.settlements.*') ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <i class="bi bi-wallet2 text-base"></i>
                    Settlement & Payout (80%)
                </a>
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800 space-y-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xs text-slate-400 hover:text-white px-2 py-1.5 transition">
                <i class="bi bi-arrow-left"></i> Lihat Website Publik
            </a>

            <div class="p-3 rounded-2xl bg-slate-800/60 border border-slate-700/50 flex items-center justify-between">
                <div class="truncate mr-2">
                    <span class="block text-xs font-bold text-white truncate">{{ auth()->user()->name ?? 'Mitra Vendor' }}</span>
                    <span class="block text-[11px] text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar Akun" class="text-slate-400 hover:text-rose-400 p-1.5 transition">
                        <i class="bi bi-box-arrow-right text-base"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <h1 class="text-lg font-bold text-slate-900">@yield('page_title', 'Portal Mitra Vendor')</h1>
            </div>

            <!-- Store Open / Close Quick Switcher -->
            <div class="flex items-center gap-3">
                @if(isset($vendor))
                    <form action="{{ route('vendor.listings.toggle_open') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ $vendor->is_open ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200' }}">
                            <span class="w-2 h-2 rounded-full {{ $vendor->is_open ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                            {{ $vendor->is_open ? 'Toko BUKA' : 'Toko TUTUP SEMENTARA' }}
                        </button>
                    </form>
                @endif

                <a href="{{ route('vendor.bookings.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-bold hover:bg-indigo-100 transition">
                    <i class="bi bi-qr-code-scan"></i> Scan / Check-In
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 sm:px-8 pt-4">
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-medium flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs sm:text-sm font-medium flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-info-circle-fill text-blue-600 text-base"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-medium flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Page Body -->
        <main class="flex-1 p-4 sm:p-8">
            @yield('content')
        </main>
    </div>

</body>
</html>
