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
<body class="min-h-screen md:h-screen md:overflow-hidden flex text-slate-800 antialiased font-sans bg-slate-100">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-950 text-slate-300 flex-shrink-0 flex flex-col justify-between hidden md:flex border-r border-slate-800 sticky top-0 h-screen overflow-y-auto z-30 [&::-webkit-scrollbar]:w-1 [&::-webkit-scrollbar-track]:bg-slate-950 [&::-webkit-scrollbar-thumb]:bg-slate-800">
        <div>
            <!-- Brand -->
            <div class="h-14 flex items-center px-5 border-b border-slate-800/80 gap-3 flex-shrink-0">
                <div class="w-8 h-8 rounded-lg bg-purple-600 flex items-center justify-center text-white shadow-md shadow-purple-600/30">
                    <i class="bi bi-shield-lock-fill text-base"></i>
                </div>
                <div>
                    <span class="font-extrabold text-white text-base tracking-tight">Titip<span class="text-purple-400">Sini</span></span>
                    <span class="block text-[9px] text-purple-400 font-mono tracking-wider uppercase font-semibold">Admin Executive</span>
                </div>
            </div>

            <!-- Nav Links -->
            <div class="px-3 py-3 space-y-0.5 text-xs font-medium">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="bi bi-speedometer2 text-sm"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.vendors.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.vendors.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <div class="flex items-center gap-2.5">
                        <i class="bi bi-person-badge-fill text-sm"></i>
                        <span>Verifikasi KYC Mitra</span>
                    </div>
                </a>

                <a href="{{ route('admin.listings.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.listings.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="bi bi-shop-window text-sm"></i>
                    <span>Moderasi Listing</span>
                </a>

                <a href="{{ route('admin.bookings.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.bookings.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="bi bi-calendar2-check text-sm"></i>
                    <span>Manajemen Booking</span>
                </a>

                <a href="{{ route('admin.disputes.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.disputes.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                    <i class="bi bi-shield-exclamation text-sm"></i>
                    <span>Pusat Sengketa</span>
                </a>

                @if(auth()->user()->isSuperadmin())
                    <a href="{{ route('admin.finance.index') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.finance.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                        <i class="bi bi-cash-stack text-sm"></i>
                        <span>Rekonsiliasi Finansial</span>
                    </a>

                    <a href="{{ route('admin.audit_logs.index') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.audit_logs.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                        <i class="bi bi-journal-text text-sm"></i>
                        <span>Jejak Audit Sistem</span>
                    </a>

                    <a href="{{ route('admin.skck_requests.index') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.skck_requests.*') ? 'bg-purple-600 text-white font-semibold shadow-md shadow-purple-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                        <i class="bi bi-shield-lock text-sm"></i>
                        <span>Izin Akses SKCK</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-slate-800/80 space-y-2 flex-shrink-0 bg-slate-950">
            <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                <a href="{{ route('vendor.dashboard') }}" class="flex items-center justify-center gap-1.5 text-slate-400 hover:text-white px-2 py-1.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 transition" title="Buka Portal Mitra">
                    <i class="bi bi-box-arrow-up-right text-[10px]"></i> Portal Mitra
                </a>
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-1.5 text-slate-400 hover:text-white px-2 py-1.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 transition" title="Website Publik">
                    <i class="bi bi-globe text-[10px]"></i> Web Publik
                </a>
            </div>

            <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
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
                    <button type="submit" title="Keluar Akun" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition">
                        <i class="bi bi-box-arrow-right text-base"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 md:h-full overflow-y-auto">
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
