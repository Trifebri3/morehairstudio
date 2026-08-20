<div class="h-full flex flex-col justify-between py-6 relative" wire:poll.10s>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-stone-200 pb-5">
            <div>
                <h3 class="text-2xl font-bold text-stone-900 mb-1 tracking-wide">Live Visual Queue</h3>
                <p class="text-stone-500 text-xs font-medium">Papan pemantauan antrean treatment hari ini secara real-time.</p>
            </div>
            <div class="flex items-center space-x-2 text-[10px] uppercase font-mono tracking-widest text-[#0A3D91] font-extrabold bg-blue-50 px-3.5 py-1.5 rounded-full border border-blue-100 animate-pulse">
                <span>Auto-Refreshes: 10s</span>
            </div>
        </div>

        <!-- Success Message -->
        @if($successMessage)
            <x-ui.alert variant="success">
                {{ $successMessage }}
            </x-ui.alert>
        @endif

        <!-- Three Column Queue Board -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-4 items-start">
            
            <!-- Column 1: Checked-In / Waiting -->
            <div class="glass-panel p-6 rounded-3xl border border-stone-200 bg-stone-50 space-y-5">
                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-green-700 flex items-center">
                        <span class="h-2 w-2 rounded-full bg-green-500 mr-2 animate-ping"></span>
                        Waiting Queue
                    </h4>
                    <x-ui.badge variant="success">
                        {{ $bookings->whereIn('status', ['checked_in', 'pending', 'confirmed'])->count() }}
                    </x-ui.badge>
                </div>

                <div class="space-y-4 overflow-y-auto max-h-[500px] pr-1">
                    @forelse($bookings->whereIn('status', ['checked_in', 'pending', 'confirmed']) as $booking)
                        <div class="glass-panel bg-white border border-stone-200 p-5 rounded-2xl hover:border-blue-500/30 transition duration-300 space-y-4 shadow-sm">
                            <div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xxs font-mono text-stone-400 font-bold uppercase tracking-wider">{{ $booking->booking_code }}</span>
                                    <span class="text-[10px] font-mono text-stone-500 font-bold">{{ substr($booking->items->first()->start_time, 0, 5) }} WIB</span>
                                </div>
                                <h5 class="font-bold text-base text-stone-900 mt-1.5">{{ $booking->customer->name }}</h5>
                                <div class="inline-flex items-center mt-2.5 bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-1 rounded-lg text-xxs font-extrabold uppercase tracking-wide">
                                    <span>{{ $booking->items->first()->service->name }} ({{ $booking->items->first()->duration }}m)</span>
                                </div>
                            </div>
                            
                            <div class="pt-3 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500 font-medium">
                                <span>Stylist: <strong class="text-stone-700">{{ $booking->stylist->name }}</strong></span>
                            </div>

                            <x-ui.button variant="primary" size="sm" class="w-full mt-1.5" wire:click="startService({{ $booking->id }})">
                                Mulai Layanan &rarr;
                            </x-ui.button>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-400">
                            <p class="text-xs font-medium">Tidak ada customer mengantre.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 2: In Progress -->
            <div class="glass-panel p-6 rounded-3xl border border-stone-200 bg-stone-50 space-y-5">
                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#0A3D91] flex items-center">
                        <span class="h-2 w-2 rounded-full bg-[#0A3D91] mr-2 animate-pulse"></span>
                        On Chair
                    </h4>
                    <x-ui.badge variant="primary">
                        {{ $bookings->where('status', 'in_progress')->count() }}
                    </x-ui.badge>
                </div>

                <div class="space-y-4 overflow-y-auto max-h-[500px] pr-1">
                    @forelse($bookings->where('status', 'in_progress') as $booking)
                        <div class="glass-panel bg-white border border-blue-200 p-5 rounded-2xl hover:border-blue-500/40 transition duration-300 space-y-4 shadow-sm">
                            <div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xxs font-mono text-stone-400 font-bold uppercase tracking-wider">{{ $booking->booking_code }}</span>
                                    <span class="text-[10px] font-mono text-blue-600 font-bold">Sedang Berjalan</span>
                                </div>
                                <h5 class="font-bold text-base text-stone-900 mt-1.5">{{ $booking->customer->name }}</h5>
                                <div class="inline-flex items-center mt-2.5 bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-1 rounded-lg text-xxs font-extrabold uppercase tracking-wide">
                                    <span>{{ $booking->items->first()->service->name }}</span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500 font-medium">
                                <span>Stylist: <strong class="text-stone-700">{{ $booking->stylist->name }}</strong></span>
                            </div>

                            <x-ui.button variant="danger" size="sm" class="w-full mt-1.5" wire:click="completeService({{ $booking->id }})">
                                Selesai Layanan ✓
                            </x-ui.button>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-400">
                            <p class="text-xs font-medium">Tidak ada treatment berjalan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 3: Completed Today -->
            <div class="glass-panel p-6 rounded-3xl border border-stone-200 bg-stone-50 space-y-5">
                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-stone-400 flex items-center">
                        <span class="h-2 w-2 rounded-full bg-stone-400 mr-2"></span>
                        Done Today
                    </h4>
                    <x-ui.badge variant="neutral">
                        {{ $bookings->where('status', 'completed')->count() }}
                    </x-ui.badge>
                </div>

                <div class="space-y-4 overflow-y-auto max-h-[500px] pr-1">
                    @forelse($bookings->where('status', 'completed') as $booking)
                        <div class="glass-panel bg-stone-50/50 border border-stone-200 p-5 rounded-2xl opacity-75 hover:opacity-100 transition duration-300 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xxs font-mono text-stone-450 font-bold uppercase tracking-wider">{{ $booking->booking_code }}</span>
                                <span class="text-[10px] text-green-600 font-extrabold uppercase font-mono">Completed</span>
                            </div>
                            <h5 class="font-bold text-base text-stone-800 mt-1">{{ $booking->customer->name }}</h5>
                            <p class="text-xxs text-stone-500 uppercase tracking-wide">
                                {{ $booking->items->first()->service->name }}
                            </p>
                            <div class="pt-2 mt-2 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500">
                                <span>Stylist: {{ $booking->stylist->name }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-400">
                            <p class="text-xs font-medium">Belum ada treatment selesai hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Back Button -->
    <div class="pt-6 border-t border-stone-200 flex justify-end mt-8">
        <x-ui.button variant="secondary" size="md" onclick="window.location.href='{{ route('tablet.dashboard') }}'">
            Kembali Ke Menu
        </x-ui.button>
    </div>
</div>
