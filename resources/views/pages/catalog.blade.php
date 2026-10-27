@extends('layouts.app')

@section('title', 'Katalog Tempat Penitipan Barang & Koper — TitipSini')
@section('meta_description', 'Cari dan bandingkan tempat penitipan koper dan barang di stasiun, bandara, dan pusat kota di Indonesia. Lengkap dengan fasilitas CCTV, AC, dan asuransi.')

@section('content')
    <!-- Header Banner -->
    <section class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white py-12 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Jaringan Mitra Resmi</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Katalog Tempat Penitipan</h1>
                <p class="text-sm text-slate-300">
                    Temukan lokasi penitipan barang terpercaya yang sesuai dengan kebutuhan Anda. Reservasi slot secara online tanpa uang muka.
                </p>
            </div>
        </div>
    </section>

    <!-- Main Catalog Filter & Listings Area -->
    <section class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                
                <!-- Filter Sidebar -->
                <aside class="lg:col-span-1">
                    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm sticky top-28 space-y-6">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <span class="font-bold text-slate-900 text-base flex items-center gap-2">
                                <i class="bi bi-funnel-fill text-indigo-600"></i> Filter Pencarian
                            </span>
                            @if(request()->anyFilled(['q', 'category', 'city', 'facility', 'sort']))
                                <a href="{{ route('catalog.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                    Reset
                                </a>
                            @endif
                        </div>

                        <form action="{{ route('catalog.index') }}" method="GET" class="space-y-5">
                            <!-- Keyword Search -->
                            <div>
                                <label for="filter-q" class="block text-xs font-bold uppercase text-slate-500 mb-2">Kata Kunci</label>
                                <div class="relative">
                                    <i class="bi bi-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                                    <input type="text" id="filter-q" name="q" value="{{ request('q') }}" placeholder="Cari nama atau alamat..." class="w-full pl-8 pr-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <!-- City Filter -->
                            <div>
                                <label for="filter-city" class="block text-xs font-bold uppercase text-slate-500 mb-2">Kota</label>
                                <select id="filter-city" name="city" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Semua Kota</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category Filter -->
                            <div>
                                <label for="filter-category" class="block text-xs font-bold uppercase text-slate-500 mb-2">Kategori Barang</label>
                                <select id="filter-category" name="category" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Facility Filter -->
                            <div>
                                <label for="filter-facility" class="block text-xs font-bold uppercase text-slate-500 mb-2">Fasilitas Khusus</label>
                                <select id="filter-facility" name="facility" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Semua Fasilitas</option>
                                    @foreach($facilities as $fac)
                                        <option value="{{ $fac->slug }}" {{ request('facility') === $fac->slug ? 'selected' : '' }}>{{ $fac->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition-all">
                                Terapkan Filter
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Listings Section -->
                <main class="lg:col-span-3 space-y-6">
                    <!-- Top Bar: Results Count & Sort -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-slate-500">
                            Menampilkan <span class="font-bold text-slate-900">{{ $listings->total() }}</span> tempat penitipan terverifikasi
                        </div>

                        <!-- Sort Select -->
                        <form action="{{ route('catalog.index') }}" method="GET" class="flex items-center gap-2 text-xs">
                            @foreach(request()->except('sort', 'page') as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <label for="catalog-sort" class="text-slate-500 whitespace-nowrap">Urutkan:</label>
                            <select id="catalog-sort" name="sort" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Harga Termurah</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                                <option value="capacity" {{ request('sort') === 'capacity' ? 'selected' : '' }}>Slot Terbanyak</option>
                            </select>
                        </form>
                    </div>

                    <!-- Listings Grid -->
                    @if($listings->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($listings as $item)
                                <div class="group bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">
                                    <!-- Image Container with Badges -->
                                    <div class="relative h-48 bg-slate-100 overflow-hidden">
                                        <img src="{{ $item->featured_image ?? 'https://images.unsplash.com/photo-1584985383910-a7a54fa9a7f8?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        
                                        <!-- Category Badge -->
                                        <div class="absolute top-3 left-3">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900/80 text-white backdrop-blur-md">
                                                {{ $item->category->name }}
                                            </span>
                                        </div>

                                        <!-- Verified Mitra Badge -->
                                        <div class="absolute top-3 right-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500 text-white shadow-md">
                                                <i class="bi bi-patch-check-fill text-[9px]"></i> Mitra Resmi
                                            </span>
                                        </div>

                                        <!-- City Pill -->
                                        <div class="absolute bottom-2.5 left-3">
                                            <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-white/90 text-slate-800 backdrop-blur-md shadow-sm flex items-center gap-1">
                                                <i class="bi bi-geo-alt-fill text-indigo-600"></i> {{ $item->city }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Card Content -->
                                    <div class="p-5 flex-grow flex flex-col justify-between space-y-4">
                                        <div>
                                            <h2 class="font-bold text-base text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1">
                                                <a href="{{ route('catalog.show', $item->slug) }}">
                                                    {{ $item->title }}
                                                </a>
                                            </h2>
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                                {{ $item->tagline ?? $item->description }}
                                            </p>
                                        </div>

                                        <!-- Facilities Chips -->
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($item->facilities->take(3) as $fac)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                                    {{ $fac->name }}
                                                </span>
                                            @endforeach
                                        </div>

                                        <!-- Capacity -->
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 border-t border-slate-100">
                                            <span class="flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full {{ $item->capacity_available > 3 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                                <span>Sisa <strong>{{ $item->capacity_available }}</strong> slot</span>
                                            </span>
                                            <span class="text-slate-400">Min. 1 Jam</span>
                                        </div>

                                        <!-- Price & Button -->
                                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                            <div>
                                                <span class="text-[10px] font-semibold text-slate-400 block uppercase">Harga</span>
                                                <div class="flex items-baseline gap-1">
                                                    <span class="text-base font-black text-indigo-600">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</span>
                                                    <span class="text-[10px] text-slate-500">/hari</span>
                                                </div>
                                            </div>
                                            <a href="{{ route('catalog.show', $item->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-all">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination Links -->
                        <div class="pt-6">
                            {{ $listings->links() }}
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="p-12 text-center rounded-3xl bg-white border border-slate-200 space-y-4">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto">
                                <i class="bi bi-search"></i>
                            </div>
                            <div class="max-w-md mx-auto space-y-1">
                                <h3 class="font-bold text-lg text-slate-900">Tidak ada tempat penitipan ditemukan</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Cobalah mengatur ulang filter atau gunakan kata kunci pencarian yang lebih umum seperti nama stasiun atau kota.
                                </p>
                            </div>
                            <div class="pt-2">
                                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset Semua Filter
                                </a>
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </section>
@endsection
