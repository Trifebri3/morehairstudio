@extends('layouts.admin')

@section('content')
<div class="space-y-6 pb-12" x-data="databaseResearchApp()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-stone-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                    Data Intelligence & Analytics
                </span>
            </div>
            <h1 class="text-2xl font-black text-stone-900 tracking-tight mt-1">Riset & Analisis Database</h1>
            <p class="text-xs text-stone-500 mt-1">
                Eksplorasi tabel-tabel kunci, tren perilaku pelanggan, utilisasi kapster, dan performa finansial MORE Hair Studio.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.database-backup') }}" class="px-3.5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl transition flex items-center gap-2">
                <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16m-5 5H9m3-3v6"/></svg>
                <span>Kelola Backup Database &rarr;</span>
            </a>
            <a href="{{ route('admin.database-research.export', ['sql' => 'SELECT * FROM bookings ORDER BY id DESC LIMIT 500']) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh Laporan Riset (CSV)</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Kapasitas Database</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">DB</span>
            </div>
            <p class="text-2xl font-black text-stone-900 mt-2">{{ $overview['size_mb'] }} <span class="text-xs font-normal text-stone-500">MB</span></p>
            <p class="text-[11px] text-stone-500 mt-1">{{ $overview['total_tables'] }} Tabel &bull; Database: <span class="font-mono text-stone-700 font-semibold">{{ $overview['database_name'] }}</span></p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Retensi Pelanggan</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">%</span>
            </div>
            <p class="text-2xl font-black text-stone-900 mt-2">{{ $customerAnalytics['retention_rate'] }}%</p>
            <p class="text-[11px] text-stone-500 mt-1">{{ $customerAnalytics['repeat_customers'] }} Pelanggan Berulang dari {{ $customerAnalytics['total_customers'] }} Total</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Total Reservasi</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">BK</span>
            </div>
            <p class="text-2xl font-black text-stone-900 mt-2">{{ number_format($bookingAnalytics['total_bookings'], 0, ',', '.') }}</p>
            <p class="text-[11px] text-stone-500 mt-1">Pembatalan / Expired: <span class="font-bold text-rose-600">{{ $bookingAnalytics['cancellation_rate'] }}%</span></p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Rata-rata Order (AOV)</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">Rp</span>
            </div>
            <p class="text-2xl font-black text-stone-900 mt-2">Rp {{ number_format($financialAnalytics['average_order_value'], 0, ',', '.') }}</p>
            <p class="text-[11px] text-stone-500 mt-1">Total Omzet Bersih: <span class="font-bold text-emerald-600">Rp {{ number_format($financialAnalytics['total_revenue'], 0, ',', '.') }}</span></p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-stone-200">
        <nav class="flex space-x-6 overflow-x-auto" aria-label="Tabs">
            <button type="button" @click="activeTab = 'overview'" class="pb-3 text-xs font-bold border-b-2 transition whitespace-nowrap"
                    :class="activeTab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-stone-500 hover:text-stone-700 hover:border-stone-300'">
                Tabel Kunci & Skema
            </button>
            <button type="button" @click="activeTab = 'customers'" class="pb-3 text-xs font-bold border-b-2 transition whitespace-nowrap"
                    :class="activeTab === 'customers' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-stone-500 hover:text-stone-700 hover:border-stone-300'">
                Pelanggan & VIP
            </button>
            <button type="button" @click="activeTab = 'bookings'" class="pb-3 text-xs font-bold border-b-2 transition whitespace-nowrap"
                    :class="activeTab === 'bookings' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-stone-500 hover:text-stone-700 hover:border-stone-300'">
                Pola Jam Sibuk & Booking
            </button>
            <button type="button" @click="activeTab = 'stylists'" class="pb-3 text-xs font-bold border-b-2 transition whitespace-nowrap"
                    :class="activeTab === 'stylists' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-stone-500 hover:text-stone-700 hover:border-stone-300'">
                Performa Kapster & Layanan
            </button>
            <button type="button" @click="activeTab = 'sql_explorer'" class="pb-3 text-xs font-bold border-b-2 transition whitespace-nowrap flex items-center gap-1.5"
                    :class="activeTab === 'sql_explorer' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-stone-500 hover:text-stone-700 hover:border-stone-300'">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                <span>SQL Query Explorer (Read-Only)</span>
            </button>
        </nav>
    </div>

    <!-- TAB 1: TABEL KUNCI & SKEMA -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-2xs">
            <div class="p-4 border-b border-stone-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-stone-900">Identifikasi Tabel-Tabel Kunci Database MORE</h3>
                    <p class="text-xs text-stone-500">Tabel inti yang menjadi fondasi data transaksi, kapster, pemesanan, dan pelanggan.</p>
                </div>
                <span class="text-xs font-mono font-bold bg-stone-100 px-3 py-1 rounded-lg text-stone-600">
                    Total {{ count($overview['table_stats']) }} Tabel Terpetakan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 text-stone-500 font-bold border-b border-stone-200 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Nama Tabel SQL</th>
                            <th class="py-3 px-4">Deskripsi Fungsi Data</th>
                            <th class="py-3 px-4 text-right">Jumlah Baris Record</th>
                            <th class="py-3 px-4 text-center">Status Integritas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($overview['table_stats'] as $item)
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-600">{{ $item['table'] }}</td>
                            <td class="py-3 px-4 font-medium text-stone-800">{{ $item['label'] }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-right text-stone-900">
                                {{ number_format($item['count'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Aktif & Relasional
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: PELANGGAN & VIP -->
    <div x-show="activeTab === 'customers'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Segmentasi Gender & Tipe -->
            <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
                <h3 class="text-sm font-bold text-stone-900">Segmentasi Pelanggan</h3>
                
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-stone-600">Pelanggan Berulang (Repeat)</span>
                            <span class="text-stone-900">{{ $customerAnalytics['repeat_customers'] }} ({{ $customerAnalytics['retention_rate'] }}%)</span>
                        </div>
                        <div class="w-full bg-stone-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $customerAnalytics['retention_rate'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-stone-600">Pelanggan Baru (New Visitor)</span>
                            <span class="text-stone-900">{{ $customerAnalytics['new_customers'] }}</span>
                        </div>
                        <div class="w-full bg-stone-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full" style="width: {{ 100 - $customerAnalytics['retention_rate'] }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-100">
                    <span class="text-[11px] font-bold text-stone-400 uppercase tracking-wider block mb-2">Sebaran Gender</span>
                    <div class="grid grid-cols-2 gap-2 text-center text-xs">
                        <div class="p-3 bg-stone-50 rounded-xl border border-stone-100">
                            <span class="text-stone-400 block text-[10px] font-bold uppercase">Pria (Male)</span>
                            <span class="text-base font-black text-stone-900 font-mono">{{ $customerAnalytics['gender_breakdown']['male'] ?? 0 }}</span>
                        </div>
                        <div class="p-3 bg-stone-50 rounded-xl border border-stone-100">
                            <span class="text-stone-400 block text-[10px] font-bold uppercase">Wanita (Female)</span>
                            <span class="text-base font-black text-stone-900 font-mono">{{ $customerAnalytics['gender_breakdown']['female'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 10 High-Value VIP Customers -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-2xs">
                <div class="p-4 border-b border-stone-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900">Top 10 Pelanggan VIP (Customer Lifetime Value)</h3>
                        <p class="text-xs text-stone-500">Pelanggan dengan akumulasi kontribusi omzet tertinggi di MORE Hair Studio.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-stone-50 text-stone-500 font-bold border-b border-stone-200 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-2.5 px-4">Kode & Nama</th>
                                <th class="py-2.5 px-4">Kontak</th>
                                <th class="py-2.5 px-4 text-center">Total Kunjungan</th>
                                <th class="py-2.5 px-4 text-right">Akumulasi Belanja</th>
                                <th class="py-2.5 px-4 text-right">Kunjungan Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($customerAnalytics['top_spenders'] as $vip)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="py-2.5 px-4">
                                    <span class="font-bold text-stone-900 block">{{ $vip->name }}</span>
                                    <span class="font-mono text-[10px] text-stone-400">{{ $vip->customer_code }}</span>
                                </td>
                                <td class="py-2.5 px-4 font-mono text-stone-600">{{ $vip->phone }}</td>
                                <td class="py-2.5 px-4 text-center font-bold font-mono">{{ $vip->total_bookings }}x</td>
                                <td class="py-2.5 px-4 text-right font-mono font-extrabold text-emerald-600">
                                    Rp {{ number_format($vip->lifetime_spent, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-4 text-right text-stone-500 font-mono text-[11px]">
                                    {{ $vip->last_visit ? \Carbon\Carbon::parse($vip->last_visit)->format('d M Y') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-stone-400 italic">Belum ada riwayat transaksi pelanggan tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: POLA JAM SIBUK & BOOKING -->
    <div x-show="activeTab === 'bookings'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Peak Hours Bar Chart -->
            <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900">Distribusi Jam Kedatangan Paling Ramai (Peak Hours)</h3>
                        <p class="text-xs text-stone-500">Membantu pengaturan shift kapster dan kapasitas kursi barber.</p>
                    </div>
                </div>

                <div class="space-y-2.5 pt-2">
                    @php
                        $maxHourVal = !empty($bookingAnalytics['peak_hours']) ? max($bookingAnalytics['peak_hours']) : 1;
                        if ($maxHourVal == 0) $maxHourVal = 1;
                    @endphp
                    @foreach($bookingAnalytics['peak_hours'] as $hour => $count)
                    <div class="flex items-center gap-3 text-xs">
                        <span class="w-12 font-mono font-bold text-stone-700 text-right">{{ $hour }}</span>
                        <div class="flex-1 bg-stone-100 h-5 rounded-lg overflow-hidden relative">
                            <div class="bg-indigo-600 h-full rounded-lg transition-all" style="width: {{ ($count / $maxHourVal) * 100 }}%"></div>
                            <span class="absolute inset-y-0 right-2 flex items-center text-[10px] font-mono font-bold text-stone-600">{{ $count }} sesi</span>
                        </div>
                    </div>
                    @endforeach
                    @if(empty($bookingAnalytics['peak_hours']))
                    <p class="text-xs text-stone-400 italic text-center py-6">Belum ada data jam sesi.</p>
                    @endif
                </div>
            </div>

            <!-- Hari Sibuk & Sumber Booking -->
            <div class="space-y-6">
                <!-- Peak Days of Week -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs space-y-3">
                    <h3 class="text-sm font-bold text-stone-900">Hari Kedatangan Terpadat (Peak Days)</h3>
                    <div class="space-y-2">
                        @php
                            $maxDayVal = !empty($bookingAnalytics['peak_days']) ? max($bookingAnalytics['peak_days']) : 1;
                            if ($maxDayVal == 0) $maxDayVal = 1;
                        @endphp
                        @foreach($bookingAnalytics['peak_days'] as $dayName => $count)
                        <div class="flex items-center gap-3 text-xs">
                            <span class="w-24 font-semibold text-stone-700">{{ $dayName }}</span>
                            <div class="flex-1 bg-stone-100 h-4 rounded-md overflow-hidden relative">
                                <div class="bg-emerald-600 h-full rounded-md" style="width: {{ ($count / $maxDayVal) * 100 }}%"></div>
                                <span class="absolute inset-y-0 right-2 flex items-center text-[9px] font-mono font-bold text-stone-600">{{ $count }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Sumber Booking: Web vs Walk-In -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs space-y-3">
                    <h3 class="text-sm font-bold text-stone-900">Kanal Pemesanan (Website vs Walk-In)</h3>
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                            <span class="text-[10px] uppercase font-bold text-indigo-700 block">Reservasi Online (Website)</span>
                            <span class="text-xl font-black text-indigo-950 font-mono mt-1 block">
                                {{ $bookingAnalytics['source_counts']['website'] ?? 0 }}
                            </span>
                        </div>
                        <div class="p-3 bg-amber-50/50 border border-amber-100 rounded-xl">
                            <span class="text-[10px] uppercase font-bold text-amber-700 block">Walk-In (Langsung Kasir)</span>
                            <span class="text-xl font-black text-amber-950 font-mono mt-1 block">
                                {{ $bookingAnalytics['source_counts']['walk_in'] ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: PERFORMA KAPSTER & LAYANAN -->
    <div x-show="activeTab === 'stylists'" class="space-y-6">
        <!-- Kapster Performance Table -->
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-2xs">
            <div class="p-4 border-b border-stone-100">
                <h3 class="text-sm font-bold text-stone-900">Performa & Kontribusi Kapster / Barber</h3>
                <p class="text-xs text-stone-500">Evaluasi total klien yang dilayani, omzet yang digenerate, dan rating kepuasan pelanggan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 text-stone-500 font-bold border-b border-stone-200 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Nama Stylist</th>
                            <th class="py-3 px-4">Spesialisasi</th>
                            <th class="py-3 px-4 text-center">Klien Dilayani</th>
                            <th class="py-3 px-4 text-right">Omzet Dihasilkan</th>
                            <th class="py-3 px-4 text-center">Rating Kepuasan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($stylistAnalytics as $st)
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="py-3 px-4 font-bold text-stone-900">{{ $st->name }}</td>
                            <td class="py-3 px-4 text-stone-600">{{ $st->specialization ?: 'General Barber' }}</td>
                            <td class="py-3 px-4 text-center font-bold font-mono">{{ $st->total_clients_served }} Sesi</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                Rp {{ number_format($st->total_revenue_generated, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 font-mono">
                                    Rating {{ $st->avg_rating ?: '5.0' }} ({{ $st->total_reviews }} ulasan)
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $st->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                                    {{ $st->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Services -->
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-2xs">
            <div class="p-4 border-b border-stone-100">
                <h3 class="text-sm font-bold text-stone-900">10 Layanan Paling Banyak Dipesan (Service Popularity)</h3>
                <p class="text-xs text-stone-500">Layanan haircut & treatment paling berkontribusi pada pendapatan MORE Hair Studio.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 text-stone-500 font-bold border-b border-stone-200 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Nama Layanan</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-right">Harga Default</th>
                            <th class="py-3 px-4 text-center">Durasi</th>
                            <th class="py-3 px-4 text-center">Frekuensi Dipesan</th>
                            <th class="py-3 px-4 text-right">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($serviceAnalytics as $srv)
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="py-3 px-4 font-bold text-stone-900">{{ $srv->name }}</td>
                            <td class="py-3 px-4 text-stone-500">{{ $srv->category_name ?: 'General' }}</td>
                            <td class="py-3 px-4 text-right font-mono">Rp {{ number_format($srv->default_price, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $srv->default_duration }} Menit</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-indigo-600">{{ $srv->total_booked_count }}x</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                Rp {{ number_format($srv->total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 5: INTERACTIVE SQL QUERY EXPLORER (READ-ONLY) -->
    <div x-show="activeTab === 'sql_explorer'" class="space-y-6">
        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-stone-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-stone-900">Eksplorasi SQL Langsung (Read-Only Data Intelligence)</h3>
                    <p class="text-xs text-stone-500">Pilih kueri analitik preset atau tuliskan kueri kustom SELECT untuk penelitian data lanjutan.</p>
                </div>
                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 self-start sm:self-auto">
                    Aman: Hanya Kueri SELECT
                </span>
            </div>

            <!-- Preset Queries Selector -->
            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Preset Kueri Analitik Cepat</label>
                <select @change="applyPreset($event.target.value)" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                    <option value="">-- Pilih Kueri Riset Siap Pakai --</option>
                    @foreach($presetQueries as $preset)
                    <option value="{{ $preset['id'] }}">{{ $preset['title'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- SQL Textarea -->
            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Editor Kueri SQL</label>
                <textarea x-model="customSql" rows="4" class="w-full font-mono text-xs p-3 bg-stone-900 text-emerald-400 rounded-xl border border-stone-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="SELECT * FROM customers LIMIT 10;"></textarea>
            </div>

            <!-- Run Button & Export -->
            <div class="flex items-center gap-3">
                <button type="button" @click="executeQuery()" :disabled="executing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs disabled:opacity-50">
                    <span x-text="executing ? 'Mengeksekusi...' : 'Jalankan Kueri SQL'"></span>
                </button>
                <a :href="'{{ route('admin.database-research.export') }}?sql=' + encodeURIComponent(customSql)" target="_blank" class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                    <span>Ekspor Hasil ke CSV</span>
                </a>
            </div>

            <!-- Execution Message -->
            <template x-if="queryMessage">
                <div class="p-3 rounded-xl text-xs font-medium" :class="querySuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                    <span x-text="queryMessage"></span>
                </div>
            </template>

            <!-- Results Table -->
            <div x-show="queryColumns.length > 0" class="mt-4 border border-stone-200 rounded-xl overflow-hidden">
                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-stone-100 text-stone-700 font-bold border-b border-stone-200 sticky top-0 uppercase tracking-wider text-[10px]">
                            <tr>
                                <template x-for="col in queryColumns" :key="col">
                                    <th class="py-2.5 px-3 whitespace-nowrap font-mono" x-text="col"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            <template x-for="(row, rIdx) in queryRows" :key="rIdx">
                                <tr class="hover:bg-stone-50">
                                    <template x-for="col in queryColumns" :key="col">
                                        <td class="py-2 px-3 whitespace-nowrap font-mono text-stone-700" x-text="row[col] !== null ? row[col] : 'NULL'"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function databaseResearchApp() {
    return {
        activeTab: 'overview',
        customSql: "SELECT c.customer_code, c.name, c.phone, COUNT(b.id) AS total_kunjungan, FORMAT(SUM(b.net_amount), 0) AS total_pengeluaran_rp FROM customers c JOIN bookings b ON c.id = b.customer_id WHERE b.status NOT IN ('cancelled', 'expired') GROUP BY c.id, c.customer_code, c.name, c.phone ORDER BY SUM(b.net_amount) DESC LIMIT 10;",
        presetQueries: @json($presetQueries),
        executing: false,
        queryMessage: '',
        querySuccess: true,
        queryColumns: [],
        queryRows: [],

        applyPreset(presetId) {
            const found = this.presetQueries.find(p => p.id === presetId);
            if (found) {
                this.customSql = found.sql;
                this.executeQuery();
            }
        },

        async executeQuery() {
            if (!this.customSql.trim()) return;
            this.executing = true;
            this.queryMessage = '';
            try {
                const res = await fetch('{{ route('admin.database-research.query') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ sql: this.customSql })
                });
                const data = await res.json();
                this.querySuccess = data.success;
                this.queryMessage = data.message;
                this.queryColumns = data.columns || [];
                this.queryRows = data.rows || [];
            } catch (err) {
                this.querySuccess = false;
                this.queryMessage = 'Terjadi kesalahan koneksi server saat mengeksekusi kueri.';
            } finally {
                this.executing = false;
            }
        }
    };
}
</script>
@endsection
