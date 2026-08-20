<div>
    <!-- Top Summary Metrics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <!-- Card 1: Total Customers -->
        <div class="bg-white border border-stone-200 p-5 rounded-2xl shadow-sm">
            <span class="text-xxs text-stone-400 font-bold uppercase tracking-wider block">Total Customer</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-2xl font-black text-stone-900">{{ $totalCustomers }}</span>
                <span class="text-xxs font-bold text-emerald-600">Aktif</span>
            </div>
            <p class="text-[10px] text-stone-500 mt-1">Total customer terekam dalam segmen terpilih.</p>
        </div>

        <!-- Card 2: New Customers -->
        <div class="bg-white border border-stone-200 p-5 rounded-2xl shadow-sm">
            <span class="text-xxs text-stone-400 font-bold uppercase tracking-wider block">Customer Baru (30 Hari)</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-2xl font-black text-stone-950">{{ $newCustomers }}</span>
                <span class="text-xxs font-bold text-blue-600">Registrasi</span>
            </div>
            <p class="text-[10px] text-stone-500 mt-1">Registrasi customer baru 30 hari terakhir.</p>
        </div>

        <!-- Card 3: Repeat Customer Rate -->
        <div class="bg-white border border-stone-200 p-5 rounded-2xl shadow-sm">
            <span class="text-xxs text-stone-400 font-bold uppercase tracking-wider block">Repeat Order Rate</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-2xl font-black text-stone-900">{{ $repeatRate }}%</span>
                <span class="text-xxs font-bold text-indigo-600">Kunjungan</span>
            </div>
            <p class="text-[10px] text-stone-500 mt-1">Customer dengan minimal 2 kali kunjungan/transaksi.</p>
        </div>

        <!-- Card 4: Average spending -->
        <div class="bg-white border border-stone-200 p-5 rounded-2xl shadow-sm">
            <span class="text-xxs text-stone-400 font-bold uppercase tracking-wider block">Rata-rata Belanja</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-lg font-black text-stone-900">Rp {{ number_format($avgSpending, 0, ',', '.') }}</span>
            </div>
            <p class="text-[10px] text-stone-500 mt-1">Rata-rata belanja per customer (bookings + POS).</p>
        </div>
    </div>

    <!-- Segmentation Alerts Block -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
        <div class="bg-emerald-50/50 border border-emerald-100 p-3.5 rounded-xl flex justify-between items-center text-xs">
            <span class="font-medium text-emerald-800">Active</span>
            <span class="font-mono font-bold text-emerald-900 bg-emerald-100 px-2 py-0.5 rounded">{{ $activeCount }}</span>
        </div>
        <div class="bg-stone-50 border border-stone-200 p-3.5 rounded-xl flex justify-between items-center text-xs">
            <span class="font-medium text-stone-700">Inactive</span>
            <span class="font-mono font-bold text-stone-900 bg-stone-200 px-2 py-0.5 rounded">{{ $inactiveCount }}</span>
        </div>
        <div class="bg-amber-50 border border-amber-200 p-3.5 rounded-xl flex justify-between items-center text-xs">
            <span class="font-medium text-amber-800">At Risk</span>
            <span class="font-mono font-bold text-amber-900 bg-amber-200 px-2 py-0.5 rounded">{{ $atRiskCount }}</span>
        </div>
        <div class="bg-red-50 border border-red-200 p-3.5 rounded-xl flex justify-between items-center text-xs">
            <span class="font-medium text-red-800">Lost</span>
            <span class="font-mono font-bold text-red-900 bg-red-200 px-2 py-0.5 rounded">{{ $lostCount }}</span>
        </div>
    </div>

    <!-- Filters & Actions Header -->
    <div class="bg-white border border-stone-200 p-6 rounded-3xl shadow-sm mb-8 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="font-black text-stone-900 text-sm uppercase tracking-wider">CRM Intelligence Filters</h3>
                <p class="text-xxs text-stone-500 mt-0.5">Saring segmentasi database customer untuk kampanye pemasaran atau ekspor laporan.</p>
            </div>
            <x-ui.button variant="primary" wire:click="exportExcel" class="h-9 px-5 text-xxs font-bold uppercase tracking-wider bg-[#0A3D91] text-white hover:bg-blue-800 transition shadow-sm rounded-xl flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Unduh Laporan Excel
            </x-ui.button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <!-- Filter Outlet -->
            <div>
                <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Scope Outlet</label>
                <select wire:model.live="filterOutlet" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700" {{ auth()->user()->role === 'outlet_admin' ? 'disabled' : '' }}>
                    <option value="">Semua Outlet</option>
                    @foreach($outlets as $o)
                        <option value="{{ $o->id }}">{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Source -->
            <div>
                <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Saluran Akuisisi</label>
                <select wire:model.live="filterSource" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700">
                    <option value="">Semua Rujukan</option>
                    @foreach(['Website', 'WhatsApp', 'Instagram', 'TikTok', 'Google', 'Google Maps', 'Referral', 'Walk-in', 'Friend / Family', 'Offline Campaign', 'Event', 'Advertisement', 'Existing Customer', 'Other'] as $src)
                        <option value="{{ $src }}">{{ $src }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Segment RFM -->
            <div>
                <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Segmen RFM</label>
                <select wire:model.live="filterSegment" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700">
                    <option value="">Semua Segmen</option>
                    @foreach(['Champions', 'Loyal Customers', 'Potential Loyalists', 'New Customers', 'Promising', 'At Risk', 'Need Attention', 'Lost Customers'] as $seg)
                        <option value="{{ $seg }}">{{ $seg }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Daftar Dari</label>
                <input type="date" wire:model.live="dateFrom" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700" />
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Daftar Sampai</label>
                <input type="date" wire:model.live="dateTo" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700" />
            </div>
        </div>
    </div>

    <!-- Main CRM Workplace -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Search & Filtered Customer List -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white border border-stone-200 p-6 rounded-3xl shadow-sm">
                <div class="mb-4">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Nama / Kode / Telepon..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-10 px-4 text-stone-750 placeholder-stone-400 focus:border-[#0A3D91] transition" />
                </div>

                <div class="space-y-2.5 overflow-y-auto max-h-[550px] pr-1.5">
                    @forelse($customerList as $cust)
                        <div wire:click="selectCustomer({{ $cust->id }})" class="p-4 rounded-2xl cursor-pointer border transition {{ $selectedCustomerId == $cust->id ? 'border-[#0A3D91] bg-blue-50/30' : 'border-stone-150 hover:border-stone-300 bg-stone-50/20' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-xs text-stone-900">{{ $cust->name }}</h4>
                                    <span class="text-[10px] text-stone-400 font-mono mt-0.5 block">{{ $cust->customer_code }}</span>
                                </div>
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[9px] font-black uppercase font-mono">{{ $cust->rfm_segment }}</span>
                            </div>
                            
                            <div class="mt-3 flex justify-between items-center text-[10px] text-stone-500 font-mono">
                                <span>TELP: {{ $cust->phone }}</span>
                                <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-bold font-sans">Loyalty: {{ $cust->loyalty_points }} pts</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-stone-400 text-xs py-8 text-center select-none">Tidak ada customer yang sesuai dengan filter.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Detail Customer Profiler & Activity Timeline -->
        <div class="lg:col-span-2">
            @if(!$selectedCustomer)
                <div class="bg-white border border-stone-200 p-12 rounded-3xl shadow-sm text-center flex flex-col items-center justify-center min-h-[480px]">
                    <div class="h-16 w-16 bg-stone-50 border border-stone-200 text-[#0A3D91] rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-stone-900 uppercase tracking-tight">Customer Profiler & Timeline</h3>
                    <p class="text-xs text-stone-500 mt-2 max-w-sm">
                        Silakan ketuk salah satu nama di daftar sebelah kiri untuk memuat detail biodata lengkap, segmen nilai RFM, riwayat pengeluaran, serta audit timeline aktivitas digital.
                    </p>
                </div>
            @else
                <div class="bg-white border border-stone-200 p-8 rounded-3xl shadow-sm space-y-8">
                    <!-- Customer header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-stone-150 pb-6">
                        <div>
                            <span class="text-[10px] text-amber-600 font-black uppercase tracking-wider block font-mono">{{ $selectedCustomer->customer_code }}</span>
                            <h2 class="text-2xl font-black text-stone-900 mt-0.5">{{ $selectedCustomer->name }}</h2>
                            <div class="flex flex-wrap gap-x-4 gap-y-2 mt-2.5 text-xxs font-mono text-stone-500">
                                <span>TELP: {{ $selectedCustomer->phone }}</span>
                                @if($selectedCustomer->email)
                                    <span>EMAIL: {{ $selectedCustomer->email }}</span>
                                @endif
                                <span>GENDER: {{ ucfirst($selectedCustomer->gender ?: 'Guest') }}</span>
                                @if($selectedCustomer->birth_date)
                                    <span>LAHIR: {{ $selectedCustomer->birth_date->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-black rounded-full uppercase tracking-wider font-mono shadow-xs">{{ $selectedCustomer->rfm['segment'] }} ({{ $selectedCustomer->rfm['rfm_code'] }})</span>
                            <span class="text-xxs font-mono text-stone-400">Daftar: {{ $selectedCustomer->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>

                    <!-- Derived Behavior Analytics -->
                    <div class="space-y-4">
                        <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold">Derived Behavior Analytics</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="bg-stone-50 p-4 rounded-2xl border">
                                <span class="text-[10px] text-stone-400 font-bold block">Total Kunjungan</span>
                                <span class="text-lg font-black text-stone-900 block mt-1">{{ $selectedCustomer->behavior['total_visits'] }} Kali</span>
                            </div>
                            <div class="bg-stone-50 p-4 rounded-2xl border">
                                <span class="text-[10px] text-stone-400 font-bold block">Total Belanja</span>
                                <span class="text-lg font-black text-emerald-600 block mt-1">Rp {{ number_format($selectedCustomer->behavior['total_spending'], 0, ',', '.') }}</span>
                            </div>
                            <div class="bg-stone-50 p-4 rounded-2xl border">
                                <span class="text-[10px] text-stone-400 font-bold block">Rata-rata Transaksi</span>
                                <span class="text-lg font-black text-[#0A3D91] block mt-1">Rp {{ number_format($selectedCustomer->behavior['average_spending'], 0, ',', '.') }}</span>
                            </div>
                            <div class="bg-stone-50 p-4 rounded-2xl border">
                                <span class="text-[10px] text-stone-400 font-bold block">Loyalty Points</span>
                                <span class="text-lg font-black text-amber-600 block mt-1 font-mono">{{ $selectedCustomer->loyalty_points }} pts</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                            <div class="bg-stone-50 p-4 rounded-2xl border text-xxs space-y-1 text-stone-700">
                                <span class="text-[10px] text-stone-400 font-bold uppercase tracking-wider block mb-1">Preferences</span>
                                <div><strong>Favorite Studio:</strong> {{ $selectedCustomer->behavior['favorite_outlet'] }}</div>
                                <div><strong>Favorite Stylist:</strong> {{ $selectedCustomer->behavior['favorite_stylist'] }}</div>
                                <div><strong>Favorite Service:</strong> {{ $selectedCustomer->behavior['favorite_service'] }}</div>
                            </div>
                            <div class="bg-stone-50 p-4 rounded-2xl border text-xxs space-y-1 text-stone-700">
                                <span class="text-[10px] text-stone-400 font-bold uppercase tracking-wider block mb-1">Kehadiran (Booking)</span>
                                <div><strong>Total Reservasi:</strong> {{ $selectedCustomer->behavior['total_bookings'] }}</div>
                                <div><strong>Tingkat Selesai:</strong> {{ $selectedCustomer->behavior['completion_rate'] }}%</div>
                                <div><strong>No Show Rate:</strong> {{ $selectedCustomer->behavior['no_show_rate'] }}%</div>
                            </div>
                            <div class="bg-stone-50 p-4 rounded-2xl border text-xxs space-y-1 text-stone-700">
                                <span class="text-[10px] text-stone-400 font-bold uppercase tracking-wider block mb-1">Informasi Rujukan</span>
                                <div><strong>Rujukan Pertama:</strong> {{ $selectedCustomer->first_acquisition_source }}</div>
                                <div><strong>Rujukan Terbaru:</strong> {{ $selectedCustomer->latest_acquisition_source }}</div>
                                @if($selectedCustomer->address)
                                    <div class="truncate"><strong>Alamat:</strong> {{ $selectedCustomer->address }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Customer Timeline -->
                    <div class="space-y-6 pt-4 border-t">
                        <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-[#0A3D91] rounded-full"></span>
                            Customer Timeline & Action Logs
                        </h4>

                        <div class="relative border-l border-stone-200 pl-6 ml-3 space-y-6">
                            @forelse($timeline as $act)
                                <div class="relative">
                                    <!-- Bullet node -->
                                    <span class="absolute -left-[31px] top-1 flex items-center justify-center w-4 h-4 rounded-full border-2 border-white {{ $act->event_type === 'transaction_created' ? 'bg-emerald-500' : ($act->event_type === 'booking_created' ? 'bg-blue-500' : 'bg-stone-500') }}"></span>

                                    <div class="bg-stone-50/50 border p-4 rounded-2xl space-y-2 text-xxs">
                                        <div class="flex justify-between items-center">
                                            <span class="font-black uppercase tracking-wider text-stone-700">{{ str_replace('_', ' ', $act->event_type) }}</span>
                                            <span class="font-mono text-stone-400">{{ \Carbon\Carbon::parse($act->event_date)->format('d/m/Y H:i') }}</span>
                                        </div>
                                        
                                        @if($act->metadata)
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-stone-500 font-mono">
                                                @foreach($act->metadata as $mKey => $mVal)
                                                    @if(is_array($mVal))
                                                        <div class="col-span-2"><strong>{{ $mKey }}:</strong> {{ json_encode($mVal) }}</div>
                                                    @else
                                                        <div><strong>{{ $mKey }}:</strong> {{ $mVal }}</div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="text-[10px] text-stone-400">Sumber: {{ ucfirst($act->source) }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-stone-400 text-xxs py-4 pl-4 select-none">Belum ada aktivitas tercatat untuk pelanggan ini.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
