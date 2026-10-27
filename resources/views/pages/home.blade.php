@extends('layouts.app')

@section('title', 'TitipSini — Platform Penitipan Barang & Koper Terpercaya di Indonesia')

@section('content')
    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-12 pb-24 lg:pt-20 lg:pb-32 bg-gradient-to-b from-indigo-900 via-slate-900 to-slate-950 text-white">
        <!-- Ambient Glow Orbs -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[350px] bg-indigo-500/20 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute top-1/3 -right-20 w-[400px] h-[300px] bg-blue-500/15 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center space-y-6">
                <!-- Trust Pill -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-indigo-300 text-xs font-semibold uppercase tracking-wider shadow-sm">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Solusi Titip Barang #1 Bebas Beban &amp; Aman
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.15]">
                    Titip Koper &amp; Barang <span class="bg-gradient-to-r from-indigo-400 via-blue-300 to-teal-300 bg-clip-text text-transparent">Aman</span>, Bayar Nanti Saat Diambil.
                </h1>

                <!-- Subheading -->
                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    Nikmati liburan dan mobilitas tanpa koper berat. Mitra berizin ketat, segel barcode digital, pengawasan CCTV 24 jam, dan dilindungi asuransi.
                </p>

                <!-- Search Filter Widget -->
                <div class="pt-6">
                    <form action="{{ route('catalog.index') }}" method="GET" class="p-3 sm:p-4 rounded-3xl bg-white/95 backdrop-blur-xl shadow-2xl shadow-indigo-950/50 border border-white text-slate-800 text-left">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                            <!-- Input Search -->
                            <div class="md:col-span-5 relative">
                                <label for="hero-search" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1 ml-1">Lokasi atau Nama Tempat</label>
                                <div class="relative flex items-center">
                                    <i class="bi bi-search absolute left-3.5 text-slate-400"></i>
                                    <input type="text" id="hero-search" name="q" placeholder="Cari stasiun, bandara, mall, area..." class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                                </div>
                            </div>

                            <!-- Dropdown Kota -->
                            <div class="md:col-span-3">
                                <label for="hero-city" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1 ml-1">Kota</label>
                                <div class="relative">
                                    <select id="hero-city" name="city" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white appearance-none cursor-pointer">
                                        <option value="">Semua Kota</option>
                                        @foreach($popularCities as $city)
                                            <option value="{{ $city }}">{{ $city }}</option>
                                        @endforeach
                                    </select>
                                    <i class="bi bi-chevron-down absolute right-3.5 top-3 text-xs text-slate-400 pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Dropdown Kategori -->
                            <div class="md:col-span-4 flex items-end gap-2">
                                <div class="w-full">
                                    <label for="hero-cat" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1 ml-1">Jenis Penitipan</label>
                                    <div class="relative">
                                        <select id="hero-cat" name="category" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white appearance-none cursor-pointer">
                                            <option value="">Semua Jenis Barang</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-3.5 top-3 text-xs text-slate-400 pointer-events-none"></i>
                                    </div>
                                </div>
                                <button type="submit" class="h-11 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 hover:scale-[1.02] transition-all whitespace-nowrap">
                                    <i class="bi bi-arrow-right"></i>
                                    <span class="hidden sm:inline">Cari</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Trust Badges Row -->
                <div class="pt-8 flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-xs sm:text-sm text-slate-300 font-medium">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-shield-check text-emerald-400 text-lg"></i>
                        <span>Asuransi TitipSini s.d Rp 10 Jt</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="bi bi-wallet2 text-blue-400 text-lg"></i>
                        <span>Bayar Nanti Saat Ambil Barang</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="bi bi-camera-video text-indigo-400 text-lg"></i>
                        <span>CCTV 24 Jam Terpantau</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Kategori Penitipan</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Simpan Berbagai Macam Kebutuhan</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                    <span>Lihat Semua Katalog</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach($categories as $category)
                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="group p-6 rounded-2xl bg-slate-50 hover:bg-indigo-50/60 border border-slate-200/80 hover:border-indigo-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-white group-hover:bg-indigo-600 text-indigo-600 group-hover:text-white flex items-center justify-center text-2xl shadow-sm transition-all duration-300">
                                @if($category->slug === 'koper-tas-wisata')
                                    <i class="bi bi-briefcase-fill"></i>
                                @elseif($category->slug === 'dokumen-berkas-penting')
                                    <i class="bi bi-file-earmark-lock2-fill"></i>
                                @elseif($category->slug === 'elektronik-gadget')
                                    <i class="bi bi-laptop-fill"></i>
                                @elseif($category->slug === 'paket-barang-ekspedisi')
                                    <i class="bi bi-box-seam-fill"></i>
                                @else
                                    <i class="bi bi-shield-fill-check"></i>
                                @endif
                            </div>
                            <h3 class="font-bold text-slate-900 group-hover:text-indigo-700 transition-colors">
                                {{ $category->name }}
                            </h3>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">
                                {{ $category->description }}
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-200/60 flex items-center justify-between text-xs font-semibold text-slate-400 group-hover:text-indigo-600">
                            <span>{{ $category->listings_count }} Lokasi</span>
                            <i class="bi bi-chevron-right group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Featured Listings Section -->
    <section class="py-16 sm:py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Rekomendasi Terbaik</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Lokasi Penitipan Terverifikasi</h2>
                    <p class="text-sm text-slate-500 mt-1">Telah lolos verifikasi standar keamanan fisik, CCTV, dan manajemen operasional.</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-700 hover:text-indigo-600 hover:border-indigo-300 shadow-sm transition-all">
                    Jelajahi Semua Tempat
                </a>
            </div>

            <!-- Listings Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($featuredListings as $listing)
                    <div class="group bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">
                        <!-- Image Container with Badges -->
                        <div class="relative h-56 bg-slate-100 overflow-hidden">
                            <img src="{{ $listing->featured_image ?? 'https://images.unsplash.com/photo-1584985383910-a7a54fa9a7f8?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <!-- Category Badge -->
                            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-900/80 text-white backdrop-blur-md">
                                    {{ $listing->category->name }}
                                </span>
                            </div>

                            <!-- Verified Mitra Badge -->
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500 text-white shadow-md">
                                    <i class="bi bi-patch-check-fill text-[11px]"></i> Mitra Resmi
                                </span>
                            </div>

                            <!-- City Overlay Pill -->
                            <div class="absolute bottom-3 left-3">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-white/90 text-slate-800 backdrop-blur-md shadow-sm flex items-center gap-1">
                                    <i class="bi bi-geo-alt-fill text-indigo-600"></i> {{ $listing->city }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 flex-grow flex flex-col justify-between space-y-4">
                            <div>
                                <h3 class="font-bold text-lg text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1">
                                    <a href="{{ route('catalog.show', $listing->slug) }}">
                                        {{ $listing->title }}
                                    </a>
                                </h3>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $listing->tagline ?? $listing->description }}
                                </p>
                            </div>

                            <!-- Facilities Chips -->
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($listing->facilities->take(3) as $facility)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600">
                                        <i class="bi bi-check2 text-emerald-600"></i> {{ $facility->name }}
                                    </span>
                                @endforeach
                                @if($listing->facilities->count() > 3)
                                    <span class="px-1.5 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-500">
                                        +{{ $listing->facilities->count() - 3 }}
                                    </span>
                                @endif
                            </div>

                            <!-- Capacity Info -->
                            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $listing->capacity_available > 5 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    <span>Tersedia <strong>{{ $listing->capacity_available }}</strong> slot</span>
                                </span>
                                <span class="text-slate-400">Min. 1 Jam</span>
                            </div>

                            <!-- Price and Action CTA -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Mulai dari</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-lg font-black text-indigo-600">Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}</span>
                                        <span class="text-xs text-slate-500 font-medium">/ hari</span>
                                    </div>
                                </div>
                                <a href="{{ route('catalog.show', $listing->slug) }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-xs font-semibold shadow-sm hover:shadow-indigo-500/20 transition-all">
                                    Pesan Slot
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Alur Penitipan Praktis</span>
                <h2 class="text-3xl font-extrabold text-slate-900">3 Langkah Mudah Menitipkan Barang</h2>
                <p class="text-sm text-slate-500">Sistem terintegrasi penuh tanpa uang muka saat pemesanan awal.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                <!-- Step 1 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 relative space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-indigo-600/30">
                        1
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Pilih Lokasi &amp; Booking</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Cari titik penitipan terdekat di stasiun, mall, atau pusat kota. Konfirmasi tanggal dan slot koper Anda <strong>tanpa perlu bayar di muka</strong>.
                    </p>
                    <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 pt-2">
                        <i class="bi bi-check-circle-fill text-emerald-500"></i> Bebas Biaya Pembatalan
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 relative space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-blue-600/30">
                        2
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Drop Barang &amp; Segel Barcode</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Datangi mitra kami, tunjukkan kode booking digital, dan barang Anda akan disegel dengan tag barcode anti-buka khusus serta dipotret kondisinya.
                    </p>
                    <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 pt-2">
                        <i class="bi bi-shield-lock-fill text-blue-500"></i> Pengawasan CCTV 24/7
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 relative space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-teal-600 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-teal-600/30">
                        3
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Bayar Aman &amp; Ambil Barang</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Saat ingin menyelesaikan masa penitipan, lakukan pembayaran online otomatis (QRIS/VA) melalui gateway resmi, lalu barang diserahkan kembali secara utuh.
                    </p>
                    <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 pt-2">
                        <i class="bi bi-credit-card-fill text-teal-500"></i> Settlement Otomatis ke Mitra
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why TitipSini Security Section -->
    <section class="py-20 bg-slate-950 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-400/20 text-indigo-300 text-xs font-semibold">
                        <i class="bi bi-shield-shaded"></i> Keamanan Nomor 1
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                        Mengapa Ribuan Pelanggan Mempercayakan Barangnya di TitipSini?
                    </h2>
                    <p class="text-slate-400 leading-relaxed text-sm sm:text-base">
                        Kami menjembatani rasa tenang Anda saat menjelajahi kota. Setiap mitra melewati proses verifikasi identitas, kepatuhan legalitas usaha, dan standar pengawasan ketat.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600/30 border border-indigo-500/40 text-indigo-400 flex items-center justify-center text-lg">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h3 class="font-bold text-white text-sm">Asuransi TitipSini</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">Kompensasi jaminan ganti rugi resmi hingga Rp 10.000.000 untuk setiap transaksi terdaftar.</p>
                        </div>
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-blue-600/30 border border-blue-500/40 text-blue-400 flex items-center justify-center text-lg">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <h3 class="font-bold text-white text-sm">Segel Digital Barcode</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">Penyegelan tas/koper dengan barcode sekali pakai agar barang tidak dapat dibuka pihak manapun.</p>
                        </div>
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-teal-600/30 border border-teal-500/40 text-teal-400 flex items-center justify-center text-lg">
                                <i class="bi bi-camera-video"></i>
                            </div>
                            <h3 class="font-bold text-white text-sm">Pengawasan Rekaman CCTV</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">Area penyimpanan dipantau kamera CCTV beresolusi tinggi selama 24 jam penuh.</p>
                        </div>
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-purple-600/30 border border-purple-500/40 text-purple-400 flex items-center justify-center text-lg">
                                <i class="bi bi-wallet-fill"></i>
                            </div>
                            <h3 class="font-bold text-white text-sm">Bayar Saat Pengambilan</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">Tidak ada resiko dana hilang di awal. Anda baru melunasi biaya saat barang siap diambil.</p>
                        </div>
                    </div>
                </div>

                <!-- Visual Card Mockup -->
                <div class="relative">
                    <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl relative space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Bukti Serah Terima Resmi</h3>
                                    <span class="text-xs text-emerald-400 font-medium">Status: Barang Tersegel Aman</span>
                                </div>
                            </div>
                            <span class="text-xs font-mono bg-slate-800 px-2.5 py-1 rounded text-slate-300">#BK-89021</span>
                        </div>

                        <div class="space-y-3 text-xs text-slate-300">
                            <div class="flex justify-between py-2 border-b border-slate-800/60">
                                <span class="text-slate-400">Penyimpan</span>
                                <span class="font-medium text-white">Siti Rahma</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-slate-800/60">
                                <span class="text-slate-400">Lokasi Mitra</span>
                                <span class="font-medium text-white">Titip Amanah Stasiun Gambir</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-slate-800/60">
                                <span class="text-slate-400">Item Dititipkan</span>
                                <span class="font-medium text-white">1x Koper Kabin Samsonite 20 Inch</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-slate-800/60">
                                <span class="text-slate-400">Kode Segel</span>
                                <span class="font-mono text-indigo-400 font-bold">SEAL-GMB-99231</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-slate-400">Total Biaya Saat Ambil</span>
                                <span class="text-emerald-400 font-bold text-sm">Rp 35.000</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-indigo-950/60 border border-indigo-800/50 flex items-center gap-3 text-xs text-indigo-200">
                            <i class="bi bi-info-circle-fill text-indigo-400 text-lg flex-shrink-0"></i>
                            <span>Barang siap diambil kapan saja dalam jam operasional mitra dengan memindai kode pengambilan di kasir.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
