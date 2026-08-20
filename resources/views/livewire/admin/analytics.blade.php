<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 border-b border-stone-200 pb-5">
        <div>
            <h2 class="text-2xl font-black text-stone-900 uppercase tracking-tight">System Analytics & Performance</h2>
            <p class="text-xs text-stone-500 font-medium mt-1">Pantau lalu lintas kunjungan, data demografi target, dan saluran rujukan pemasaran real-time.</p>
        </div>
        <x-ui.button variant="primary" wire:click="exportToExcel" class="h-[42px] px-6 rounded-xl text-xs font-bold uppercase tracking-wider shadow-md bg-[#0A3D91] text-white hover:bg-blue-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Unduh Laporan Excel
        </x-ui.button>
    </div>

    <div class="space-y-6 font-sans">
        <!-- Stats Summary grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Revenue Card -->
            <div class="bg-white border border-stone-200 p-6 rounded-2xl shadow-sm flex flex-col justify-between">
                <span class="text-[10px] uppercase font-extrabold tracking-widest text-stone-400">Total Revenue</span>
                <span class="text-2xl font-black text-stone-900 mt-2 font-mono">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                <span class="text-[10px] text-green-600 font-bold mt-1">Confirmed & Paid Sesi</span>
            </div>

            <!-- Bookings Card -->
            <div class="bg-white border border-stone-200 p-6 rounded-2xl shadow-sm flex flex-col justify-between">
                <span class="text-[10px] uppercase font-extrabold tracking-widest text-stone-400">Total Bookings</span>
                <span class="text-2xl font-black text-stone-900 mt-2 font-mono">{{ $totalBookings }}</span>
                <span class="text-[10px] text-stone-500 font-medium mt-1">{{ $completedBookings }} Completed</span>
            </div>

            <!-- Customers Card -->
            <div class="bg-white border border-stone-200 p-6 rounded-2xl shadow-sm flex flex-col justify-between">
                <span class="text-[10px] uppercase font-extrabold tracking-widest text-stone-400">CRM Clients</span>
                <span class="text-2xl font-black text-stone-900 mt-2 font-mono">{{ $totalCustomers }}</span>
                <span class="text-[10px] text-stone-550 font-medium mt-1">Registered profiles</span>
            </div>

            <!-- Ratings Card -->
            <div class="bg-white border border-stone-200 p-6 rounded-2xl shadow-sm flex flex-col justify-between">
                <span class="text-[10px] uppercase font-extrabold tracking-widest text-stone-400">Average Rating</span>
                <span class="text-2xl font-black text-stone-900 mt-2 font-mono">{{ number_format($averageRating, 1) }} / 5.0</span>
                <span class="text-[10px] text-blue-600 font-bold mt-1">From client reviews</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Outlet Comparison Card -->
            <div class="lg:col-span-2 bg-white border border-stone-200 p-6 rounded-2xl shadow-sm space-y-4">
                <h3 class="font-bold text-stone-900 text-sm uppercase tracking-wider">Outlet Performance</h3>
                <div class="overflow-x-auto rounded-xl border border-stone-150">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-150 text-stone-500 font-bold uppercase tracking-wider">
                                <th class="py-3 px-4">Outlet Name</th>
                                <th class="py-3 px-4 text-center">Bookings</th>
                                <th class="py-3 px-4 text-right">Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @foreach($outletStats as $stat)
                                <tr class="hover:bg-stone-50/50 transition">
                                    <td class="py-3 px-4 font-bold text-stone-900">{{ $stat['name'] }}</td>
                                    <td class="py-3 px-4 text-center font-mono font-medium">{{ $stat['bookings_count'] }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-[#0A3D91]">Rp {{ number_format($stat['revenue'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Booking Statuses Card -->
            <div class="bg-white border border-stone-200 p-6 rounded-2xl shadow-sm space-y-4">
                <h3 class="font-bold text-stone-900 text-sm uppercase tracking-wider">Booking Status Distribution</h3>
                <div class="space-y-3">
                    @foreach(['pending' => 'bg-stone-100 text-stone-600', 'confirmed' => 'bg-blue-50 text-blue-600', 'checked_in' => 'bg-amber-50 text-amber-600', 'in_progress' => 'bg-indigo-50 text-indigo-600', 'completed' => 'bg-green-50 text-green-700', 'cancelled' => 'bg-red-50 text-red-700'] as $status => $color)
                        <div class="flex justify-between items-center text-xs">
                            <span class="capitalize font-medium text-stone-500">{{ str_replace('_', ' ', $status) }}</span>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded font-mono font-bold {{ $color }}">
                                    {{ $statusStats[$status] ?? 0 }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Stylist Leaderboard Card -->
        <div class="bg-white border border-stone-200 p-6 rounded-2xl shadow-sm space-y-4">
            <h3 class="font-bold text-stone-900 text-sm uppercase tracking-wider">Top Stylists Leaderboard</h3>
            <div class="overflow-x-auto rounded-xl border border-stone-150">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-150 text-stone-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Stylist</th>
                            <th class="py-3 px-4">Specialization</th>
                            <th class="py-3 px-4 text-center">Total Sessions Completed</th>
                            <th class="py-3 px-4 text-right">Average Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @foreach($stylistStats as $stat)
                            <tr class="hover:bg-stone-50/50 transition">
                                <td class="py-3 px-4 font-bold text-stone-900">{{ $stat['name'] }}</td>
                                <td class="py-3 px-4 text-stone-600">{{ $stat['specialization'] ?? '-' }}</td>
                                <td class="py-3 px-4 text-center font-mono font-medium">{{ $stat['bookings_count'] }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-amber-600">★ {{ number_format($stat['rating'], 1) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rich Traffic & Demographics Analytics Grid -->
        <!-- Traffic & Category Insights Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Card: Web Traffic & Search Insights -->
            <div class="bg-white border border-stone-200 p-8 rounded-3xl shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-stone-150 pb-4">
                    <h3 class="font-bold text-stone-900 text-sm uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0A3D91]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Web Traffic & Search Insights
                    </h3>
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xxs font-black rounded-full font-mono">Views: {{ $totalPageViews }}</span>
                </div>

                <!-- Popular Pages -->
                <div class="space-y-3">
                    <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-[#0A3D91] rounded-full"></span>
                        Halaman Populer (Page Views)
                    </h4>
                    <div class="space-y-2.5">
                        @forelse($popularPages as $page)
                            <div class="flex justify-between items-center text-xs border-b border-stone-100 pb-2">
                                <span class="font-mono text-stone-600 truncate max-w-[280px]">{{ $page->page_url }}</span>
                                <span class="font-mono font-bold text-stone-900 bg-stone-50 px-2 py-0.5 rounded border border-stone-150">{{ $page->count }} visits</span>
                            </div>
                        @empty
                            <p class="text-stone-400 text-xxs py-2">Belum ada kunjungan tercatat.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Popular Searches -->
                <div class="space-y-3 pt-2">
                    <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-[#0A3D91] rounded-full"></span>
                        Kata Kunci Sering Dicari
                    </h4>
                    <div class="space-y-2.5">
                        @forelse($popularSearches as $search)
                            <div class="flex justify-between items-center text-xs border-b border-stone-100 pb-2">
                                <span class="text-stone-700 font-medium font-serif">"{{ $search->search_query }}"</span>
                                <span class="font-mono font-bold text-stone-900 bg-amber-50 text-amber-700 px-2.5 py-0.5 rounded border border-amber-100">{{ $search->count }} kali</span>
                            </div>
                        @empty
                            <p class="text-stone-400 text-xxs py-2">Belum ada kata kunci pencarian hari ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Card: Service Category Popularity (Grafik Kategori Layanan) -->
            <div class="bg-white border border-stone-200 p-8 rounded-3xl shadow-sm space-y-6">
                <div class="border-b border-stone-150 pb-4">
                    <h3 class="font-bold text-stone-900 text-sm uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Grafik Popularitas Kategori Layanan
                    </h3>
                </div>

                <div class="space-y-6">
                    @forelse($categoryStats as $cStat)
                        @php
                            $totalCatsBookings = $categoryStats->sum('count') ?: 1;
                            $pct = round(($cStat->count / $totalCatsBookings) * 100);
                            
                            // Color mapping based on category names
                            $barColor = 'bg-[#0A3D91]';
                            if (strpos(strtolower($cStat->name), 'color') !== false) {
                                $barColor = 'bg-purple-600';
                            } elseif (strpos(strtolower($cStat->name), 'treat') !== false) {
                                $barColor = 'bg-emerald-600';
                            }
                        @endphp
                        <div class="space-y-2">
                            <div class="flex justify-between items-end text-xs">
                                <div>
                                    <span class="font-bold text-stone-900 uppercase text-[11px] tracking-tight block font-sans">{{ $cStat->name }}</span>
                                    <span class="text-stone-400 text-xxs block font-medium mt-0.5">{{ $cStat->count }} Bookings ({{ $pct }}%)</span>
                                </div>
                                <span class="font-mono font-bold text-stone-900 bg-stone-50 border border-stone-150 px-2 py-0.5 rounded">Rp {{ number_format($cStat->revenue, 0, ',', '.') }}</span>
                            </div>
                            <div class="w-full bg-stone-100 h-2.5 rounded-full overflow-hidden">
                                <div class="{{ $barColor }} h-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-stone-400 text-xs text-center py-12 select-none">Belum ada pemesanan layanan terdaftar.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Demographics & Target Segments Redesign (Highly Detailed Layout) -->
        <div class="bg-white border border-stone-200 p-8 rounded-3xl shadow-sm space-y-6">
            <div class="border-b border-stone-150 pb-4">
                <h3 class="font-bold text-stone-900 text-sm uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Demographics & Target Segments
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Marketing Referral Channels -->
                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-[#0A3D91] rounded-full animate-ping"></span>
                        Saluran Rujukan (Referral)
                    </h4>
                    <div class="space-y-3">
                        @foreach(['Instagram', 'WhatsApp', 'Google', 'Facebook', 'TikTok', 'Direct'] as $chan)
                            @php
                                $count = $channelStats[$chan] ?? 0;
                                $total = array_sum($channelStats) ?: 1;
                                $percentage = round(($count / $total) * 100);
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-xxs font-bold">
                                    <span class="text-stone-700">{{ $chan }}</span>
                                    <span class="font-mono text-stone-900">{{ $count }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="w-full bg-stone-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-indigo-600 h-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Col 2: Devices -->
                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold">Devices Used</h4>
                    <div class="space-y-3">
                        @foreach(['Desktop', 'Mobile', 'Tablet'] as $d)
                            @php
                                $count = $deviceStats[$d] ?? 0;
                                $total = array_sum($deviceStats) ?: 1;
                                $percentage = round(($count / $total) * 100);
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-xxs font-bold">
                                    <span class="text-stone-700">{{ $d }}</span>
                                    <span class="font-mono text-stone-900">{{ $percentage }}%</span>
                                </div>
                                <div class="w-full bg-stone-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-blue-600 h-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Col 3: Locations -->
                <div class="space-y-4">
                    <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold">Geografis Pengakses</h4>
                    <div class="space-y-3">
                        @forelse($locationStats as $loc => $count)
                            @php
                                $totalLocs = array_sum($locationStats) ?: 1;
                                $percentage = round(($count / $totalLocs) * 100);
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-xxs font-bold">
                                    <span class="text-stone-700">{{ $loc }}</span>
                                    <span class="font-mono text-stone-900">{{ $count }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="w-full bg-stone-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-stone-400 text-xxs py-2">Belum ada data lokasi.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Col 4: Gender & Age Demographics -->
                <div class="space-y-6">
                    <!-- Gender nested -->
                    <div class="space-y-3">
                        <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold">Gender Split</h4>
                        <div class="space-y-3 text-xxs">
                            @php
                                $maleCount = $genderStats['male'] ?? 0;
                                $femaleCount = $genderStats['female'] ?? 0;
                                $genderTotal = ($maleCount + $femaleCount) ?: 1;
                                $malePct = round(($maleCount / $genderTotal) * 100);
                                $femalePct = round(($femaleCount / $genderTotal) * 100);
                            @endphp
                            <!-- Male -->
                            <div class="space-y-1">
                                <div class="flex justify-between font-bold">
                                    <span class="text-blue-600">Male (Laki-laki)</span>
                                    <span class="font-mono">{{ $malePct }}% ({{ $maleCount }})</span>
                                </div>
                                <div class="w-full bg-stone-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-blue-500 h-full" style="width: {{ $malePct }}%"></div>
                                </div>
                            </div>
                            <!-- Female -->
                            <div class="space-y-1">
                                <div class="flex justify-between font-bold">
                                    <span class="text-pink-500">Female (Perempuan)</span>
                                    <span class="font-mono">{{ $femalePct }}% ({{ $femaleCount }})</span>
                                </div>
                                <div class="w-full bg-stone-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-pink-500 h-full" style="width: {{ $femalePct }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Age brackets nested -->
                    <div class="space-y-3 pt-2">
                        <h4 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold">Usia Pengunjung</h4>
                        <div class="space-y-2.5 text-xxs font-mono text-stone-700">
                            @foreach($ageStats as $bracket => $count)
                                <div class="flex justify-between items-center border-b border-stone-100 pb-1">
                                    <span class="font-sans text-stone-500 font-medium">Usia {{ $bracket }}</span>
                                    <span class="font-bold text-stone-900 bg-stone-50 px-2 py-0.5 rounded border border-stone-150">{{ $count }} visits</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Access Logs & Audit Trail -->
        <div class="bg-white border border-stone-200 p-6 rounded-2xl shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-stone-900 text-sm uppercase tracking-wider">Log Akses Keamanan & Kebijakan Privasi</h3>
                <span class="text-xxs uppercase tracking-widest font-mono text-stone-400">Audit trail compliance log</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-stone-150">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-150 text-stone-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4 w-44">Waktu Akses</th>
                            <th class="py-3 px-4">Alamat IP</th>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4 text-center">Saluran Rujukan</th>
                            <th class="py-3 px-4">Halaman Dibuka</th>
                            <th class="py-3 px-4">Perangkat</th>
                            <th class="py-3 px-4">Browser</th>
                            <th class="py-3 px-4 text-center">Persetujuan Cookie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white text-stone-700">
                        @forelse($securityLogs as $log)
                            @php
                                $isAnonymized = ($log->ip_address === '127.x.x.x');
                            @endphp
                            <tr class="hover:bg-stone-50/50 transition">
                                <td class="py-3 px-4 font-mono text-[10px] text-stone-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-[11px] {{ $isAnonymized ? 'text-stone-400' : 'text-emerald-600' }}">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="py-3 px-4 font-bold text-stone-800">
                                    {{ $log->user ? $log->user->name : 'Guest (Tamu)' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[9px] uppercase font-black font-mono">
                                        {{ $log->source_channel ?: 'Direct' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-stone-500 text-[11px]">{{ $log->page_url }}</td>
                                <td class="py-3 px-4">{{ $log->device }}</td>
                                <td class="py-3 px-4 font-bold text-xxs uppercase tracking-wider text-stone-400">{{ $log->browser }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($isAnonymized)
                                        <span class="px-2 py-0.5 bg-stone-100 text-stone-500 rounded text-[9px] uppercase font-black">Declined / Anonim</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[9px] uppercase font-black">Accepted</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-stone-400 text-xs">Belum ada log aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
