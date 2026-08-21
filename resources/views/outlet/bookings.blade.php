@extends('layouts.admin')

@section('page_title')
    Outlet Bookings Manager
@endsection

@section('content')
<div class="space-y-6">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
        <form method="GET" action="{{ route('outlet.bookings') }}" class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
            <div class="flex-grow md:max-w-xs">
                <x-ui.input placeholder="Search by booking code, customer name or phone..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
            </div>
            <div class="w-full md:w-48">
                <x-ui.select name="statusFilter" onchange="this.form.submit()">
                    <option value="">-- All Statuses --</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $statusFilter === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="checked_in" {{ $statusFilter === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </x-ui.select>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-stone-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Stylist</th>
                        <th class="py-3.5 px-4">Layanan</th>
                        <th class="py-3.5 px-4">Tanggal & Waktu</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-stone-50/50 transition text-stone-700">
                            <td class="py-3 px-4 font-mono font-bold text-stone-900">{{ $booking->booking_code }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-stone-900 block">{{ $booking->customer->name }}</span>
                                <span class="text-stone-400 font-mono block mt-0.5">{{ $booking->customer->phone }}</span>
                            </td>
                            <td class="py-3 px-4 font-medium text-stone-600">{{ $booking->stylist ? $booking->stylist->name : 'Any Stylist' }}</td>
                            <td class="py-3 px-4">
                                {{ $booking->items->first() && $booking->items->first()->service ? $booking->items->first()->service->name : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="block">{{ $booking->booking_date->format('d M Y') }}</span>
                                <span class="font-mono text-stone-500 block mt-0.5">{{ substr($booking->booking_time, 0, 5) }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase 
                                    @if($booking->status === 'completed') bg-emerald-50 text-emerald-700 border border-emerald-100
                                    @elseif($booking->status === 'cancelled') bg-stone-100 text-stone-500 border border-stone-200
                                    @else bg-blue-50 text-blue-700 border border-blue-100 @endif">
                                    {{ str_replace('_', ' ', $booking->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex justify-end space-x-1.5">
                                    @if($booking->status === 'pending')
                                        <form method="POST" action="{{ route('outlet.bookings.status', $booking->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="px-2.5 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xxs font-bold transition">Confirm</button>
                                        </form>
                                    @endif
                                    @if(in_array($booking->status, ['pending', 'confirmed']))
                                        <form method="POST" action="{{ route('outlet.bookings.status', $booking->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="checked_in">
                                            <button type="submit" class="px-2.5 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xxs font-bold transition">Check In</button>
                                        </form>
                                    @endif
                                    @if($booking->status === 'checked_in')
                                        <form method="POST" action="{{ route('outlet.bookings.status', $booking->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="px-2.5 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xxs font-bold transition">Start</button>
                                        </form>
                                    @endif
                                    @if($booking->status === 'in_progress')
                                        <form method="POST" action="{{ route('outlet.bookings.status', $booking->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="px-2.5 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xxs font-bold transition">Complete</button>
                                        </form>
                                    @endif
                                    @if(!in_array($booking->status, ['completed', 'cancelled']))
                                        <form method="POST" action="{{ route('outlet.bookings.status', $booking->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="px-2.5 py-1.5 bg-red-50 text-red-750 hover:bg-red-100 border border-red-200 rounded-lg text-xxs font-bold transition">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-stone-400">No bookings found for this outlet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
