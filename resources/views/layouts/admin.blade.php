<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') - TitipSini</title>

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
    <aside class="w-64 bg-slate-950 text-slate-300 flex-shrink-0 flex flex-col justify-between hidden md:flex border-r border-slate-800">
        <div>
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800 gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-600 flex items-center justify-center text-white shadow-md shadow-purple-600/30">
                    <i class="bi bi-shield-lock-fill text-lg"></i>
                </div>
                <div>
                    <span class="font-extrabold text-white text-lg tracking-tight">Titip<span class="text-purple-400">Sini</span></span>
                    <span class="block text-[10px] text-purple-400 font-mono tracking-wider uppercase font-semibold">Admin Executive</span>
                </div>
            </div>

            <!-- Nav Links -->
            <div class="px-4 py-6 space-y-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="bi bi-speedometer2 text-base"></i>
                    Dashboard
                </a>

                <a href="{{ route('admin.vendors.index') }}"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.vendors.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-person-badge-fill text-base"></i>
                        <span>Verifikasi KYC Mitra</span>
                    </div>
                </a>

                <a href="{{ route('admin.listings.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.listings.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="bi bi-shop-window text-base"></i>
                    Moderasi Listing
                </a>

                @if(auth()->user()->isSuperadmin())
                    <a href="{{ route('admin.finance.index') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.finance.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                        <i class="bi bi-cash-stack text-base"></i>
                        Rekonsiliasi Finansial
                    </a>

                    <a href="{{ route('admin.audit_logs.index') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.audit_logs.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                        <i class="bi bi-journal-text text-base"></i>
                        Jejak Audit Sistem
                    </a>

                    <a href="{{ route('admin.skck_requests.index') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.skck_requests.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                        <i class="bi bi-shield-lock text-base"></i>
                        Izin Akses SKCK
                    </a>
                @endif
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800 space-y-2">
            <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-2 text-xs text-slate-400 hover:text-white px-2 py-1.5 transition">
                <i class="bi bi-box-arrow-up-right"></i> Buka Portal Mitra
            </a>
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xs text-slate-400 hover:text-white px-2 py-1.5 transition">
                <i class="bi bi-globe"></i> Website Publik
            </a>

            <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                <div class="truncate mr-2">
                    <span class="block text-xs font-bold text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</span>
                    <span class="block text-[10px] text-purple-400 font-mono uppercase">{{ auth()->user()->role->label() ?? 'Admin' }}</span>
                    @if(auth()->user()->assignedRegion)
                        <span class="inline-block text-[9px] text-amber-300 font-medium truncate mt-0.5" title="{{ auth()->user()->assignedRegion->name }}">
                            <i class="bi bi-geo-alt"></i> {{ auth()->user()->assignedRegion->code }}
                        </span>
                    @elseif(!auth()->user()->isSuperadmin())
                        <span class="inline-block text-[9px] text-rose-400 font-medium mt-0.5">
                            <i class="bi bi-exclamation-triangle"></i> Belum ada wilayah
                        </span>
                    @endif
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
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-20 shadow-sm">
            <div class="flex items-center gap-4">
                <h1 class="text-lg font-bold text-slate-900">@yield('page_title', 'Admin Executive Panel')</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                    <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                    {{ auth()->user()->role->label() ?? 'Admin' }}
                </span>
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
