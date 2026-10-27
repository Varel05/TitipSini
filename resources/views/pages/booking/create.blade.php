@extends('layouts.app')

@section('title', 'Form Pemesanan Penitipan — ' . $listing->title)

@section('content')
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-indigo-600">Beranda</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <a href="{{ route('catalog.show', $listing->slug) }}" class="hover:text-indigo-600 truncate">{{ $listing->title }}</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-bold">Form Reservasi</span>
            </div>

            <!-- Page Heading -->
            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Form Reservasi Tempat Penitipan</h1>
                <p class="text-sm text-slate-500">Konfirmasi slot Anda sekarang secara gratis. Pembayaran baru dilunasi saat serah terima pengambilan barang.</p>
            </div>

            <!-- Booking Form Grid -->
            <form action="{{ route('booking.store', $listing->slug) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                @csrf

                <!-- Left Column: Form Fields (7 cols) -->
                <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-sm space-y-6">
                    
                    <!-- Section 1: Data Pemesan -->
                    <div class="space-y-4">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                            <i class="bi bi-person-fill text-indigo-600"></i> Informasi Kontak Pemesan
                        </h2>

                        <div>
                            <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nama Lengkap Sesuai KTP *</label>
                            <input type="text" id="customer_name" name="customer_name" required value="{{ old('customer_name', auth()->user()?->name ?? 'Siti Rahma') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('customer_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="customer_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">No. WhatsApp / Telepon *</label>
                                <input type="text" id="customer_phone" name="customer_phone" required value="{{ old('customer_phone', auth()->user()?->phone ?? '085712345678') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('customer_phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer_email" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Alamat Email *</label>
                                <input type="email" id="customer_email" name="customer_email" required value="{{ old('customer_email', auth()->user()?->email ?? 'customer@titipsini.com') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('customer_email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Jadwal & Durasi -->
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                            <i class="bi bi-calendar3 text-indigo-600"></i> Rencana Waktu &amp; Durasi
                        </h2>

                        <div>
                            <label for="check_in_plan_at" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Perkiraan Waktu Tiba Drop Barang *</label>
                            <input type="datetime-local" id="check_in_plan_at" name="check_in_plan_at" required value="{{ old('check_in_plan_at', now()->addHours(2)->format('Y-m-d\TH:i')) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('check_in_plan_at') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="duration_type" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Skema Waktu</label>
                                <select id="duration_type" name="duration_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="day" data-rate="{{ $listing->price_per_day }}">Hitungan Hari (Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}/hari)</option>
                                    @if($listing->price_per_hour)
                                        <option value="hour" data-rate="{{ $listing->price_per_hour }}">Hitungan Jam (Rp {{ number_format($listing->price_per_hour, 0, ',', '.') }}/jam)</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label for="duration_units" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Berapa Hari/Jam?</label>
                                <input type="number" id="duration_units" name="duration_units" min="1" max="60" value="{{ old('duration_units', 1) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Rincian Barang -->
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                            <i class="bi bi-box-seam text-indigo-600"></i> Rincian Barang Titipan
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label for="item_name" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nama / Jenis Barang *</label>
                                <input type="text" id="item_name" name="item_name" required value="{{ old('item_name', '1x Koper Kabin 20 Inch Silver') }}" placeholder="Contoh: Koper 24 inch, ransel laptop, tas golf..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="total_items" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Jumlah Unit *</label>
                                <input type="number" id="total_items" name="total_items" min="1" max="{{ min(10, $listing->capacity_available) }}" value="{{ old('total_items', 1) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label for="customer_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Catatan Tambahan untuk Mitra (Opsional)</label>
                            <textarea id="customer_notes" name="customer_notes" rows="2" placeholder="Contoh: Barang mudah pecah, rencana ambil jam 19.00 malam..." class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('customer_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Summary Card & Action (5 cols) -->
                <div class="lg:col-span-5 sticky top-28 space-y-4">
                    <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-lg space-y-5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block border-b border-slate-100 pb-3">
                            Ringkasan Penitipan
                        </span>

                        <!-- Listing Info Box -->
                        <div class="flex items-center gap-3">
                            <img src="{{ $listing->featured_image ?? 'https://images.unsplash.com/photo-1584985383910-a7a54fa9a7f8?auto=format&fit=crop&w=200&q=80' }}" alt="{{ $listing->title }}" class="w-16 h-16 rounded-2xl object-cover bg-slate-100 flex-shrink-0">
                            <div class="space-y-0.5">
                                <h3 class="font-bold text-sm text-slate-900 line-clamp-1">{{ $listing->title }}</h3>
                                <p class="text-xs text-slate-500 line-clamp-1"><i class="bi bi-geo-alt-fill text-indigo-600"></i> {{ $listing->city }}</p>
                                <span class="inline-block text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                    Mitra: {{ $listing->vendorProfile->business_name }}
                                </span>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2 text-xs">
                            <div class="flex justify-between text-slate-600">
                                <span>Tarif Dasar</span>
                                <span id="rate-display" class="font-semibold text-slate-900">Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Durasi Penitipan</span>
                                <span id="units-display" class="font-semibold text-slate-900">1 Hari</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Total Barang</span>
                                <span id="items-display" class="font-semibold text-slate-900">1 Unit</span>
                            </div>
                            <div class="flex justify-between text-slate-500 text-[11px]">
                                <span>Asuransi Barang &amp; CCTV</span>
                                <span class="font-bold text-emerald-600">GRATIS (Rp 0)</span>
                            </div>
                            <div class="border-t border-indigo-200/80 pt-2 flex justify-between text-sm font-black text-indigo-950">
                                <span>Estimasi Saat Ambil</span>
                                <span id="total-display" class="text-base text-indigo-600">Rp {{ number_format($listing->price_per_day, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Zero Down Payment Notice -->
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <i class="bi bi-check-circle-fill text-emerald-600"></i> Tidak Dikenakan Biaya Sekarang
                            </div>
                            <p class="text-[11px] text-emerald-800/80 leading-relaxed">
                                Pesanan ini gratis saat reservasi. Anda baru membayar via QRIS/VA saat mengambil barang dari mitra.
                            </p>
                        </div>

                        <!-- Submit CTA -->
                        <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 hover:scale-[1.01] transition-all">
                            <i class="bi bi-shield-check"></i>
                            <span>Konfirmasi Reservasi (Gratis)</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Recalculation Script -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const durationType = document.getElementById('duration_type');
            const durationUnits = document.getElementById('duration_units');
            const totalItems = document.getElementById('total_items');
            const rateDisplay = document.getElementById('rate-display');
            const unitsDisplay = document.getElementById('units-display');
            const itemsDisplay = document.getElementById('items-display');
            const totalDisplay = document.getElementById('total-display');

            function updateSummary() {
                const opt = durationType.options[durationType.selectedIndex];
                const rate = parseFloat(opt.getAttribute('data-rate')) || 0;
                const units = Math.max(1, parseInt(durationUnits.value) || 1);
                const items = Math.max(1, parseInt(totalItems.value) || 1);

                const isDay = durationType.value === 'day';
                rateDisplay.textContent = 'Rp ' + rate.toLocaleString('id-ID') + (isDay ? '/hari' : '/jam');
                unitsDisplay.textContent = units + (isDay ? ' Hari' : ' Jam');
                itemsDisplay.textContent = items + ' Unit';

                const total = rate * units * items;
                totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
            }

            if (durationType && durationUnits && totalItems) {
                durationType.addEventListener('change', updateSummary);
                durationUnits.addEventListener('input', updateSummary);
                totalItems.addEventListener('input', updateSummary);
            }
        });
    </script>
    @endpush
@endsection
