@extends('layouts.admin')

@section('page_title')
    Super Admin Control Panel
@endsection

@section('content')
    <!-- Clean Hero Banner -->
    <div class="glass-panel p-8 rounded-3xl mb-10 flex flex-col md:flex-row justify-between items-start md:items-center bg-white border border-stone-200 relative overflow-hidden">
        <div class="space-y-2">
            <h2 class="text-2xl font-bold tracking-wide text-stone-900">Selamat Datang di Portal Super Admin</h2>
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

    <!-- Recent Bookings Table -->
    <div class="glass-panel p-8 rounded-3xl border border-stone-200 bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-stone-900">Recent Bookings</h3>
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
                            <td class="py-4 px-5 font-bold text-stone-800">{{ $booking->customer->name }}</td>
                            <td class="py-4 px-5 text-stone-600">{{ $booking->outlet->name }}</td>
                            <td class="py-4 px-5 text-stone-600 font-medium">{{ $booking->stylist->name }}</td>
                            <td class="py-4 px-5 text-stone-500">{{ $booking->booking_date->format('d M Y') }}</td>
                            <td class="py-4 px-5 text-right font-mono font-bold text-stone-900">Rp {{ number_format($booking->net_amount, 0, ',', '.') }}</td>
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
            <h3 class="text-lg font-bold text-stone-900">System Users (Impersonation)</h3>
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
                            <td class="py-4 px-5 font-bold text-stone-800">{{ $usr->name }}</td>
                            <td class="py-4 px-5 font-mono text-stone-500">{{ $usr->email }}</td>
                            <td class="py-4 px-5">
                                <x-ui.badge variant="{{ $usr->role === 'super_admin' ? 'success' : 'neutral' }}">
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
