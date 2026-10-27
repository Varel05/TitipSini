@extends('layouts.app')

@section('title', $listing->title . ' — TitipSini')
@section('meta_description', $listing->tagline ?? $listing->description)

@section('content')
    <!-- Breadcrumbs -->
    <nav class="bg-white border-b border-slate-200 py-3 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-indigo-600 transition-colors">Beranda</a>
            <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
            <a href="{{ route('catalog.index') }}" class="hover:text-indigo-600 transition-colors">Katalog</a>
            <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-slate-800 font-medium truncate">{{ $listing->title }}</span>
        </div>
    </nav>

    <!-- Main Detail Section -->
    <section class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Column: Content & Gallery (8 cols) -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- Title & Header Info -->
                    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $listing->category->name }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="bi bi-patch-check-fill text-emerald-600"></i> Mitra Resmi Terverifikasi
                            </span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                <i class="bi bi-shield-lock-fill text-indigo-500"></i> Asuransi s.d Rp 10 Juta
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ $listing->title }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-4 text-xs sm:text-sm text-slate-500 pt-1">
                            <span class="flex items-center gap-1 text-slate-700 font-medium">
                                <i class="bi bi-geo-alt-fill text-indigo-600"></i>
                                <span>{{ $listing->address }}</span>
                            </span>
                            <span class="text-slate-300">•</span>
                            <span class="flex items-center gap-1">
                                <i class="bi bi-clock-history text-slate-400"></i>
                                <span>{{ $listing->city }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Main Image Gallery -->
                    <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                        <div class="relative h-72 sm:h-96 rounded-2xl overflow-hidden bg-slate-100">
                            <img src="{{ $listing->featured_image ?? 'https://images.unsplash.com/photo-1584985383910-a7a54fa9a7f8?auto=format&fit=crop&w=1200&q=80' }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                            <div class="absolute bottom-4 left-4">
                                <span class="px-3 py-1.5 rounded-xl bg-slate-950/80 text-white backdrop-blur-md text-xs font-semibold flex items-center gap-1.5">
                                    <i class="bi bi-shield-check text-emerald-400"></i> Area Terproteksi CCTV 24 Jam
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Description Block -->
                    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="bi bi-text-paragraph text-indigo-600"></i> Tentang Fasilitas Penitipan Ini
                        </h2>
                        <div class="prose prose-slate max-w-none text-sm text-slate-600 leading-relaxed space-y-3">
                            <p>{{ $listing->description }}</p>
                        </div>
                    </div>

                    <!-- Facilities Grid -->
                    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="bi bi-stars text-indigo-600"></i> Fasilitas &amp; Keamanan Terjamin
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            @foreach($listing->facilities as $facility)
                                <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div class="w-9 h-9 rounded-xl bg-white text-indigo-600 flex items-center justify-center text-lg shadow-sm flex-shrink-0">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800">{{ $facility->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Operational Hours Block -->
                    @if($listing->operational_hours)
                        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="bi bi-clock-history text-indigo-600"></i> Jadwal Jam Operasional Mitra
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                @php
                                    $dayNames = [
                                        'monday' => 'Senin',
                                        'tuesday' => 'Selasa',
                                        'wednesday' => 'Rabu',
                                        'thursday' => 'Kamis',
                                        'friday' => 'Jumat',
                                        'saturday' => 'Sabtu',
                                        'sunday' => 'Minggu',
                                    ];
                                @endphp
                                @if(is_array($listing->operational_hours))
                                    @foreach($listing->operational_hours as $day => $time)
                                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <span class="font-medium text-slate-700">{{ $dayNames[$day] ?? ucfirst(str_replace('_', ' ', $day)) }}</span>
                                            <span class="font-bold text-indigo-600">
                                                @if(is_array($time))
                                                    {{ $time['open'] ?? '08:00' }} - {{ $time['close'] ?? '21:00' }} WIB
                                                @else
                                                    {{ $time }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 col-span-2">
                                        <span class="font-bold text-indigo-600">{{ $listing->formatted_operational_hours }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Rules & Safety Restrictions -->
                    <div class="bg-amber-50/70 p-6 sm:p-8 rounded-3xl border border-amber-200/80 space-y-4">
                        <div class="flex items-center gap-2.5 text-amber-900 font-bold text-base">
                            <i class="bi bi-exclamation-triangle-fill text-amber-600 text-xl"></i>
                            <span>Aturan &amp; Ketentuan Penitipan</span>
                        </div>
                        <div class="text-xs text-amber-950/80 leading-relaxed whitespace-pre-line space-y-1">
                            {{ $listing->rules ?? "1. Dilarang menitipkan barang terlarang (senjata api, narkotika, zat mudah meledak/terbakar).\n2. Dilarang menitipkan hewan peliharaan atau makanan basah yang mudah busuk.\n3. Setiap barang wajib dilakukan pemeriksaan fisik visual dan difoto oleh petugas mitra saat serah terima." }}
                        </div>
                    </div>

                    <!-- Vendor Info Profile Card -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-bold shadow-md shadow-indigo-500/20">
                                <i class="bi bi-shop"></i>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dikelola Oleh</span>
                                <h3 class="font-bold text-base text-slate-900">{{ $listing->vendorProfile->business_name }}</h3>
                                <p class="text-xs text-slate-500">Mitra Terverifikasi Sejak {{ $listing->vendorProfile->created_at->format('M Y') }}</p>
                            </div>
                        </div>
                        <div class="text-center sm:text-right">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                <i class="bi bi-shield-fill-check"></i> Akun Legal Terverifikasi
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sticky Booking Widget (4 cols) -->
                <div class="lg:col-span-4 sticky top-28 space-y-4">
                    <div class="bg-white p-6 rounded-3xl border-2 border-indigo-600/30 shadow-xl shadow-indigo-950/5 space-y-6">
                        <!-- Pricing Highlight -->
                        <div class="border-b border-slate-100 pb-5">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tarif Resmi</span>
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-3xl font-black text-indigo-600">Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}</span>
                                <span class="text-sm font-semibold text-slate-500">/ hari</span>
                            </div>
                            @if($listing->price_per_hour)
                                <span class="text-xs text-slate-400 font-medium block mt-1">
                                    Atau Rp {{ number_format($listing->price_per_hour, 0, ',', '.') }} / jam (Fleksibel)
                                </span>
                            @endif
                        </div>

                        <!-- Capacity Notice -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Slot Ketersediaan:</span>
                            <span class="font-bold text-emerald-600 flex items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i> Tersedia {{ $listing->capacity_available }} Unit
                            </span>
                        </div>

                        <!-- Interactive Calculator Script Container -->
                        <div class="space-y-4 text-xs font-medium text-slate-700">
                            <div>
                                <label for="duration-type" class="block font-bold text-slate-800 mb-1">Skema Durasi</label>
                                <select id="duration-type" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold cursor-pointer">
                                    <option value="day" data-rate="{{ $listing->price_per_day }}">Harian (Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}/hari)</option>
                                    @if($listing->price_per_hour)
                                        <option value="hour" data-rate="{{ $listing->price_per_hour }}">Per Jam (Rp {{ number_format($listing->price_per_hour, 0, ',', '.') }}/jam)</option>
                                    @endif
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="duration-count" class="block font-bold text-slate-800 mb-1">Berapa Hari/Jam?</label>
                                    <input type="number" id="duration-count" min="1" max="30" value="1" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-center">
                                </div>
                                <div>
                                    <label for="item-count" class="block font-bold text-slate-800 mb-1">Jumlah Barang</label>
                                    <input type="number" id="item-count" min="1" max="{{ $listing->capacity_available }}" value="1" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-center">
                                </div>
                            </div>

                            <!-- Cost Breakdown Box -->
                            <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 space-y-2 pt-3">
                                <div class="flex justify-between text-slate-600 text-xs">
                                    <span>Estimasi Biaya Penitipan</span>
                                    <span id="subtotal-display" class="font-bold text-slate-900">Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-slate-500 text-[11px]">
                                    <span>Asuransi Barang (Garansi)</span>
                                    <span class="font-bold text-emerald-600">GRATIS</span>
                                </div>
                                <div class="border-t border-indigo-200/80 pt-2 flex justify-between text-sm font-black text-indigo-950">
                                    <span>Total Saat Diambil</span>
                                    <span id="total-display" class="text-indigo-600 text-base">Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Zero Down Payment Reassurance -->
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <i class="bi bi-shield-check text-emerald-600"></i> Bebas Bayar Di Muka
                            </div>
                            <p class="text-[11px] text-emerald-800/80 leading-relaxed">
                                Anda baru melunasi pembayaran secara online (QRIS/Transfer VA) saat mengambil barang kembali di lokasi mitra.
                            </p>
                        </div>

                        <!-- CTA Booking Action -->
                        <a href="{{ route('booking.create', $listing->slug) }}" class="w-full py-3.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 hover:scale-[1.01] transition-all">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>Pesan Tempat Sekarang</span>
                        </a>

                        <div class="text-center">
                            <span class="text-[11px] text-slate-400 font-medium flex items-center justify-center gap-1">
                                <i class="bi bi-lock-fill text-slate-400"></i> Verifikasi Kode Unik &amp; Segel Barcode
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Calculation Script -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const durationType = document.getElementById('duration-type');
            const durationCount = document.getElementById('duration-count');
            const itemCount = document.getElementById('item-count');
            const subtotalDisplay = document.getElementById('subtotal-display');
            const totalDisplay = document.getElementById('total-display');

            function calculatePrice() {
                const selectedOption = durationType.options[durationType.selectedIndex];
                const rate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
                const count = Math.max(1, parseInt(durationCount.value) || 1);
                const items = Math.max(1, parseInt(itemCount.value) || 1);

                const total = rate * count * items;
                const formatted = 'Rp ' + total.toLocaleString('id-ID');

                subtotalDisplay.textContent = formatted;
                totalDisplay.textContent = formatted;
            }

            if (durationType && durationCount && itemCount) {
                durationType.addEventListener('change', calculatePrice);
                durationCount.addEventListener('input', calculatePrice);
                itemCount.addEventListener('input', calculatePrice);
            }
        });
    </script>
    @endpush
@endsection
