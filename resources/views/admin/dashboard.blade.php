@extends('layouts.admin')

@section('page_title')
    Super Admin Control Panel
@endsection

@section('content')
    <!-- Clean Hero Banner -->
    <div class="glass-panel p-8 rounded-3xl mb-10 flex flex-col md:flex-row justify-between items-start md:items-center bg-white border border-stone-200 relative overflow-hidden">
        <div class="space-y-2">
            <h2 class="text-2xl font-display font-bold tracking-wide text-stone-900">Selamat Datang di Portal Super Admin</h2>
            <p class="text-xs text-stone-500 max-w-xl leading-relaxed">
                Kelola seluruh performa salon, konfigurasi sistem dinamis, integrasi WhatsApp, dan sinkronisasi data outlet real-time di bawah satu kontrol panel terpusat.
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-2 text-xxs font-mono uppercase tracking-widest text-[#0A3D91] font-extrabold bg-blue-50 border border-blue-100 px-4 py-2 rounded-xl">
            <span>Sistem Aktif</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <!-- Revenue Card -->
        <x-ui.card subtitle="Total Revenue" title="Rp {{ number_format($totalRevenue, 0, ',', '.') }}">
            <div class="flex items-center space-x-1.5 mt-2 text-[10px] text-green-600 font-extrabold uppercase tracking-wide">
                <span>Sum of completed net payments</span>
            </div>
        </x-ui.card>

        <!-- Bookings Card -->
        <x-ui.card subtitle="Total Bookings" title="{{ $totalBookings }}">
            <div class="flex items-center space-x-1.5 mt-2 text-[10px] text-stone-450 font-extrabold uppercase tracking-wide">
                <span>Active online and walk-in orders</span>
            </div>
        </x-ui.card>

        <!-- Customers Card -->
        <x-ui.card subtitle="Customers" title="{{ $totalCustomers }}">
            <div class="flex items-center space-x-1.5 mt-2 text-[10px] text-stone-450 font-extrabold uppercase tracking-wide">
                <span>Registered CRM profiles</span>
            </div>
        </x-ui.card>

        <!-- Outlets Card -->
        <x-ui.card subtitle="Active Outlets" title="{{ $totalOutlets }}">
            <div class="flex items-center space-x-1.5 mt-2 text-[10px] text-stone-450 font-extrabold uppercase tracking-wide">
                <span>Bandung and Jakarta SCBD</span>
            </div>
        </x-ui.card>
    </div>

    <!-- Peringkat Sumber Informasi (Dari Mana Tahu MORE) -->
    <div class="glass-panel p-6 sm:p-8 rounded-3xl border border-stone-200 bg-white mb-10 shadow-xs">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b border-stone-100">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#faede7] text-[#c9512d] flex items-center justify-center font-bold flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-stone-900 font-display">Sumber Informasi (Dari Mana Tahu MORE)</h3>
                        <p class="text-xs text-stone-500 mt-0.5">
                            Peringkat saluran promosi yang paling banyak mendatangkan reservasi & customer.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
                @if(isset($referralStats['top_source']) && $referralStats['top_source'] && $referralStats['total'] > 0)
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-bold shadow-2xs">
                        <span>🏆 Terbanyak:</span>
                        <span class="text-stone-950 font-black underline decoration-amber-400">{{ $referralStats['top_source']['channel'] }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-200/80 text-amber-950 text-[10px] font-mono">
                            {{ $referralStats['top_source']['count'] }} ({{ $referralStats['top_source']['percentage'] }}%)
                        </span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200 text-stone-500 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#c9512d] animate-pulse"></span>
                        <span>Menunggu data survei dari reservasi baru</span>
                    </div>
                @endif

                <a href="{{ route('admin.crm') }}" class="text-xs font-bold text-[#0A3D91] hover:underline flex items-center gap-1 flex-shrink-0">
                    Lihat CRM &rarr;
                </a>
            </div>
        </div>

        @if($referralStats['total'] > 0)
            <!-- Visual Segmented Proportion Bar -->
            <div class="mb-6 space-y-2">
                <div class="flex justify-between items-center text-[11px] font-semibold text-stone-500">
                    <span>Distribusi Proporsi Sumber Promosi (Total: {{ $referralStats['total'] }} Respon)</span>
                    <span class="font-mono text-stone-700">100%</span>
                </div>
                <div class="w-full h-3 bg-stone-100 rounded-full overflow-hidden flex">
                    @foreach($referralStats['rankings'] as $ref)
                        @if($ref['count'] > 0)
                            <div class="{{ $ref['styling']['bar'] ?? 'bg-stone-500' }} h-full transition-all duration-500" 
                                 style="width: {{ $ref['percentage'] }}%" 
                                 title="{{ $ref['channel'] }}: {{ $ref['count'] }} ({{ $ref['percentage'] }}%)">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 7 Channels Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
            @foreach($referralStats['rankings'] as $ref)
                <div class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between relative overflow-hidden {{ $ref['is_top'] ? 'border-amber-400 bg-amber-50/30 shadow-xs ring-1 ring-amber-300' : 'border-stone-200 bg-[#fafaf9] hover:bg-white hover:border-stone-300' }}">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono font-black {{ $ref['is_top'] ? 'bg-amber-400 text-stone-900 shadow-2xs' : ($ref['rank'] === 2 ? 'bg-stone-200 text-stone-700' : 'bg-stone-150 text-stone-500') }}">
                                #{{ $ref['rank'] }}
                            </span>
                            <span class="text-base select-none">{{ $ref['styling']['icon'] ?? '📌' }}</span>
                        </div>

                        <span class="font-bold text-xs text-stone-900 block truncate" title="{{ $ref['channel'] }}">
                            {{ $ref['channel'] }}
                        </span>

                        @if($ref['is_top'])
                            <span class="inline-block mt-1 px-1.5 py-0.5 text-[8px] font-black uppercase tracking-wider rounded bg-amber-200 text-amber-900 border border-amber-300">
                                🏆 Terbanyak
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 pt-2 border-t border-stone-200/50">
                        <div class="flex items-baseline justify-between mb-1.5">
                            <div class="flex items-baseline gap-1">
                                <span class="font-mono font-black text-lg text-stone-900">{{ $ref['count'] }}</span>
                                <span class="text-[10px] text-stone-400">order</span>
                            </div>
                            <span class="font-mono text-xs font-bold {{ $ref['is_top'] ? 'text-amber-800' : 'text-stone-500' }}">
                                {{ $ref['percentage'] }}%
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-stone-200/60 h-1.5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $ref['styling']['bar'] ?? 'bg-stone-500' }}" style="width: {{ max(3, $ref['percentage']) }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(!empty($referralStats['custom_details']))
            <!-- Detail Jawaban 'Lainnya' -->
            <div class="mt-6 pt-4 border-t border-stone-100">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400 block mb-2">
                    Detail Isian Pelanggan (Pilihan Lainnya):
                </span>
                <div class="flex flex-wrap gap-2">
                    @foreach($referralStats['custom_details'] as $text => $cnt)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-stone-200 text-xs text-stone-700 shadow-2xs">
                            <span class="font-medium font-sans">"{{ $text }}"</span>
                            <span class="px-1.5 py-0.5 rounded-full bg-stone-100 text-stone-900 text-[10px] font-mono font-bold">{{ $cnt }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Bookings Table -->
    <div class="glass-panel p-8 rounded-3xl border border-stone-200 bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-stone-900 font-display">Recent Bookings</h3>
            <span class="text-xxs uppercase tracking-widest font-mono text-stone-400">Live booking activity feed</span>
        </div>
        
        <div class="overflow-x-auto rounded-xl border border-stone-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                        <th class="py-4 px-5">Code</th>
                        <th class="py-4 px-5">Customer</th>
                        <th class="py-4 px-5">Studio</th>
                        <th class="py-4 px-5">Stylist</th>
                        <th class="py-4 px-5">Date / Time</th>
                        <th class="py-4 px-5 text-right">Price</th>
                        <th class="py-4 px-5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @foreach($recentBookings as $booking)
                        <tr class="hover:bg-stone-50/70 transition duration-150 text-stone-700">
                            <td class="py-4 px-5 font-mono text-[#0A3D91] font-bold tracking-wide">{{ $booking->booking_code }}</td>
                            <td class="py-4 px-5 font-bold text-stone-800">{{ $booking->customer?->name ?? 'Guest Customer' }}</td>
                            <td class="py-4 px-5 text-stone-600">{{ $booking->outlet?->name ?? '-' }}</td>
                            <td class="py-4 px-5 text-stone-600 font-medium">{{ $booking->stylist?->name ?? 'Any Stylist' }}</td>
                            <td class="py-4 px-5 text-stone-500">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : '-' }}</td>
                            <td class="py-4 px-5 text-right font-mono font-bold text-stone-900">Rp {{ number_format($booking->net_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="py-4 px-5 text-center">
                                <x-ui.badge variant="{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'primary') }}">
                                    {{ $booking->status }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Users Impersonation Section -->
    <div class="glass-panel p-8 rounded-3xl border border-stone-200 bg-white mt-10">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-stone-900 font-display">System Users (Impersonation)</h3>
            <span class="text-xxs uppercase tracking-widest font-mono text-stone-400">Impersonate shop admins or stylists</span>
        </div>
        
        <div class="overflow-x-auto rounded-xl border border-stone-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                        <th class="py-4 px-5">Name</th>
                        <th class="py-4 px-5">Email</th>
                        <th class="py-4 px-5">Role</th>
                        <th class="py-4 px-5">Assigned Studio</th>
                        <th class="py-4 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @foreach($users as $usr)
                        <tr class="hover:bg-stone-50/70 transition duration-150 text-stone-700">
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    @if($usr->isStylist() && $usr->stylist)
                                        <img src="{{ $usr->stylist->display_photo }}" alt="{{ $usr->name }}" class="w-8 h-8 rounded-lg object-cover border border-stone-200 shadow-sm flex-shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-stone-100 border border-stone-200 flex items-center justify-center font-extrabold text-stone-600 text-xs flex-shrink-0">
                                            {{ substr($usr->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <span class="font-bold text-stone-900 block">{{ $usr->name }}</span>
                                        @if($usr->isStylist() && $usr->stylist)
                                            <a href="{{ route('admin.stylists', ['edit' => $usr->stylist->id]) }}" class="text-[10px] text-blue-600 hover:underline font-medium">
                                                Profil Stylist: {{ $usr->stylist->specialization ?? 'Stylist' }} &rarr;
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-mono text-stone-500">{{ $usr->email }}</td>
                            <td class="py-4 px-5">
                                <x-ui.badge variant="{{ $usr->role === 'super_admin' ? 'success' : ($usr->role === 'stylist' ? 'info' : 'neutral') }}">
                                    {{ str_replace('_', ' ', $usr->role) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-4 px-5 text-stone-600 font-medium">{{ $usr->outlet ? $usr->outlet->name : '-' }}</td>
                            <td class="py-4 px-5 text-right">
                                @if($usr->id !== auth()->id())
                                    <a href="{{ route('impersonate.start', ['id' => $usr->id]) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded text-[10px] uppercase tracking-wider shadow-sm transition">
                                        Masuk Sebagai User
                                    </a>
                                @else
                                    <span class="text-stone-400 font-medium italic">Current Account</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
