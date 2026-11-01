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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
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

    <!-- Peringkat Sumber Rujukan (Darimana Tahu MORE) -->
    <div class="glass-panel p-8 rounded-3xl border border-stone-200 bg-white mb-10 shadow-xs">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-stone-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#c9512d] animate-pulse"></span>
                    <h3 class="text-lg font-bold text-stone-900 font-display">Peringkat Sumber Informasi (Dari Mana Tahu MORE)</h3>
                </div>
                <p class="text-xs text-stone-500 mt-1">
                    Statistik real-time saluran promosi yang paling banyak mendatangkan reservasi & customer.
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if(isset($referralStats['top_source']) && $referralStats['top_source'])
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                        <span>🏆 Terbanyak:</span>
                        <span class="text-stone-900 font-extrabold underline decoration-amber-400">{{ $referralStats['top_source']['channel'] }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-200/70 text-amber-900 text-[10px] font-mono">
                            {{ $referralStats['top_source']['count'] }} ({{ $referralStats['top_source']['percentage'] }}%)
                        </span>
                    </div>
                @endif
                <a href="{{ route('admin.analytics') }}" class="text-xs font-bold text-[#0A3D91] hover:underline flex items-center gap-1">
                    Lihat Analitik Lengkap &rarr;
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($referralStats['rankings'] as $ref)
                <div class="p-4 rounded-2xl border transition-all duration-200 {{ $ref['is_top'] ? 'border-amber-300 bg-amber-50/20 shadow-xs' : 'border-stone-200 bg-stone-50/40 hover:bg-white' }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black font-mono {{ $ref['rank'] === 1 ? 'bg-amber-400 text-amber-950 shadow-xs' : ($ref['rank'] === 2 ? 'bg-stone-300 text-stone-800' : ($ref['rank'] === 3 ? 'bg-amber-700/30 text-amber-900' : 'bg-stone-100 text-stone-500')) }}">
                                #{{ $ref['rank'] }}
                            </span>
                            <span class="font-bold text-xs text-stone-900">{{ $ref['channel'] }}</span>
                            @if($ref['is_top'])
                                <span class="px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded bg-amber-100 text-amber-800 border border-amber-300">
                                    Paling Banyak
                                </span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-xs text-stone-900">{{ $ref['count'] }}</span>
                            <span class="text-[10px] text-stone-400 font-mono">({{ $ref['percentage'] }}%)</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-stone-100 h-2 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $ref['is_top'] ? 'bg-gradient-to-r from-amber-500 to-[#c9512d]' : ($ref['styling']['bar'] ?? 'bg-stone-600') }}" style="width: {{ max(4, $ref['percentage']) }}%"></div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-6 text-stone-400 text-xs italic">
                    Belum ada data survei rujukan yang terekam.
                </div>
            @endforelse
        </div>

        @if(!empty($referralStats['custom_details']))
            <!-- Detail Jawaban 'Lainnya' -->
            <div class="mt-6 pt-4 border-t border-stone-100">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400 block mb-2">
                    Detail Isian Pelanggan (Pilihan Lainnya):
                </span>
                <div class="flex flex-wrap gap-2">
                    @foreach($referralStats['custom_details'] as $text => $cnt)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-stone-200 text-xs text-stone-700">
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
