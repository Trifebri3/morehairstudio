<div>
    @slot('page_title')
        Outlet Bookings Manager
    @endslot

    <div class="space-y-6">
        @if(session()->has('message'))
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        @endif

        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
                <div class="flex-grow md:max-w-xs">
                    <x-ui.input placeholder="Search by booking code, customer name or phone..." wire:model.live="search" />
                </div>
                <div class="w-full md:w-48">
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="">-- All Statuses --</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="checked_in">Checked In</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </x-ui.select>
                </div>
            </div>

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
                                    <x-ui.badge variant="{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'cancelled' ? 'neutral' : 'success') }}">
                                        {{ str_replace('_', ' ', $booking->status) }}
                                    </x-ui.badge>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex justify-end space-x-1.5">
                                        @if($booking->status === 'pending')
                                            <x-ui.button variant="outline" size="sm" wire:click="updateStatus({{ $booking->id }}, 'confirmed')">
                                                Confirm
                                            </x-ui.button>
                                        @endif
                                        @if(in_array($booking->status, ['pending', 'confirmed']))
                                            <x-ui.button variant="outline" size="sm" wire:click="updateStatus({{ $booking->id }}, 'checked_in')">
                                                Check In
                                            </x-ui.button>
                                        @endif
                                        @if($booking->status === 'checked_in')
                                            <x-ui.button variant="outline" size="sm" wire:click="updateStatus({{ $booking->id }}, 'in_progress')">
                                                Start
                                            </x-ui.button>
                                        @endif
                                        @if($booking->status === 'in_progress')
                                            <x-ui.button variant="outline" size="sm" wire:click="updateStatus({{ $booking->id }}, 'completed')">
                                                Complete
                                            </x-ui.button>
                                        @endif
                                        @if(!in_array($booking->status, ['completed', 'cancelled']))
                                            <x-ui.button variant="danger" size="sm" wire:click="updateStatus({{ $booking->id }}, 'cancelled')">
                                                Cancel
                                            </x-ui.button>
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
</div>
