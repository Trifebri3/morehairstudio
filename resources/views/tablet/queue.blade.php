@extends('layouts.tablet')

@section('content')
<div class="h-full flex flex-col justify-between py-6 relative">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-stone-200 pb-5">
            <div>
                <h3 class="text-2xl font-bold text-stone-900 mb-1 tracking-wide">Live Visual Queue</h3>
                <p class="text-stone-500 text-xs font-medium">Papan pemantauan antrean treatment hari ini secara real-time.</p>
            </div>
            <div class="flex items-center space-x-2 text-[10px] uppercase font-mono tracking-widest text-[#c9512d] font-extrabold bg-[#faede7] px-3.5 py-1.5 rounded-full border border-[#faede7]">
                <span class="w-1.5 h-1.5 rounded-full bg-[#c9512d] animate-ping"></span>
                <span>Auto-Refreshes: 10s</span>
            </div>
        </div>

        <!-- Success Message -->
        @if(session()->has('message'))
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        @endif

        <!-- Three Column Queue Board -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 items-start">
            
            <!-- Column 1: Waiting for Customer Check-In -->
            <div class="bg-white p-6 rounded-3xl border border-stone-200 space-y-5 shadow-2xs">
                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-blue-800 flex items-center">
                        <span class="h-2 w-2 rounded-full bg-blue-500 mr-2"></span>
                        Menunggu Check-In
                    </h4>
                    <span class="px-2.5 py-0.5 bg-blue-50 text-blue-800 rounded-full font-bold text-xxs font-mono">
                        {{ $bookings->whereIn('status', ['pending', 'confirmed'])->count() }}
                    </span>
                </div>

                <div class="space-y-4 overflow-y-auto max-h-[500px] pr-1">
                    @forelse($bookings->whereIn('status', ['pending', 'confirmed']) as $booking)
                        <div class="bg-stone-50/70 border border-stone-200 p-5 rounded-2xl hover:border-stone-400 transition duration-300 space-y-4 shadow-2xs">
                            <div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xxs font-mono text-stone-400 font-bold uppercase tracking-wider">{{ $booking->booking_code }}</span>
                                    <span class="text-[10px] font-mono text-stone-500 font-bold">{{ substr($booking->items->first()?->start_time ?? '00:00', 0, 5) }} WIB</span>
                                </div>
                                <h5 class="font-bold text-base text-stone-900 mt-1.5">{{ $booking->customer->name }}</h5>
                                <div class="inline-flex items-center mt-2.5 bg-stone-100 text-stone-700 border border-stone-200 px-2.5 py-1 rounded-lg text-xxs font-extrabold uppercase tracking-wide">
                                    <span>{{ $booking->items->first()?->service?->name ?? '-' }} ({{ $booking->service_duration_minutes ?? $booking->calculateServiceDuration() }}m)</span>
                                </div>
                            </div>
                            
                            <div class="pt-3 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500 font-medium">
                                <span>Stylist: <strong class="text-stone-700">{{ $booking->stylist?->name ?? 'Any Stylist' }}</strong></span>
                            </div>

                            <a href="{{ route('tablet.check-in', ['searchQuery' => $booking->booking_code]) }}" 
                               class="w-full mt-1.5 py-2 px-4 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs uppercase tracking-wider text-center block transition">
                                Cek-In Customer &rarr;
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-400">
                            <p class="text-xs font-medium">Tidak ada customer menunggu check-in.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 2: In Progress / On Chair -->
            <div class="bg-white p-6 rounded-3xl border border-stone-200 space-y-5 shadow-2xs">
                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#c9512d] flex items-center">
                        <span class="h-2 w-2 rounded-full bg-[#c9512d] mr-2 animate-ping"></span>
                        On Chair (Sedang Berjalan)
                    </h4>
                    <span class="px-2.5 py-0.5 bg-[#faede7] text-[#c9512d] rounded-full font-bold text-xxs font-mono">
                        {{ $bookings->whereIn('status', ['checked_in', 'in_progress'])->count() }}
                    </span>
                </div>

                <div class="space-y-4 overflow-y-auto max-h-[500px] pr-1">
                    @forelse($bookings->whereIn('status', ['checked_in', 'in_progress']) as $booking)
                        <div class="bg-[#faede7]/30 border border-[#f4cbba] p-5 rounded-2xl transition duration-300 space-y-4 shadow-2xs">
                            <div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xxs font-mono text-stone-500 font-bold uppercase tracking-wider">{{ $booking->booking_code }}</span>
                                    <span class="text-[9px] font-mono uppercase bg-[#c9512d] text-white px-2 py-0.5 rounded font-extrabold tracking-wider">
                                        Auto-Selesai
                                    </span>
                                </div>
                                <h5 class="font-bold text-base text-stone-900 mt-1.5">{{ $booking->customer->name }}</h5>
                                <div class="inline-flex items-center mt-2 bg-[#faede7] text-[#c9512d] border border-[#faede7] px-2.5 py-1 rounded-lg text-xxs font-extrabold uppercase tracking-wide">
                                    <span>{{ $booking->items->first()?->service?->name ?? '-' }} ({{ $booking->service_duration_minutes ?? $booking->calculateServiceDuration() }} mnt)</span>
                                </div>
                            </div>

                            <!-- Live Timing & Countdown Box -->
                            <div class="p-3 bg-white rounded-xl border border-stone-200 space-y-2">
                                <div class="flex justify-between items-center text-xs font-mono">
                                    <span class="text-stone-500 text-[11px]">Waktu Layanan:</span>
                                    <span class="font-bold text-stone-900">
                                        @if($booking->service_start_at && $booking->service_end_at)
                                            {{ $booking->service_start_at->format('H:i') }} - {{ $booking->service_end_at->format('H:i') }} WIB
                                        @else
                                            {{ $booking->service_duration_minutes ?? 45 }} Menit
                                        @endif
                                    </span>
                                </div>
                                
                                @if($booking->service_start_at && $booking->service_end_at)
                                    <div class="w-full bg-stone-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-[#c9512d] h-full transition-all duration-500" style="width: {{ $booking->service_progress_percentage }}%"></div>
                                    </div>
                                    <div class="flex justify-between items-center text-[10px] text-stone-500">
                                        <span>Progres: <strong>{{ $booking->service_progress_percentage }}%</strong></span>
                                        <span class="font-bold text-[#c9512d]">Sisa: {{ $booking->remaining_service_minutes }} Menit</span>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500 font-medium">
                                <span>Stylist: <strong class="text-stone-700">{{ $booking->stylist?->name ?? 'Any Stylist' }}</strong></span>
                            </div>

                            <form method="POST" action="{{ route('tablet.queue.complete', $booking->id) }}">
                                @csrf
                                <button type="submit" class="w-full py-2 px-3 bg-stone-200 hover:bg-stone-300 text-stone-700 text-xxs font-bold uppercase tracking-wider rounded-xl transition">
                                    Selesai Lebih Cepat (Manual Override)
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-400">
                            <p class="text-xs font-medium">Tidak ada treatment sedang berjalan.</p>
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
                    <span class="px-2.5 py-0.5 bg-stone-150 text-stone-700 rounded font-bold text-xxs font-mono">
                        {{ $bookings->where('status', 'completed')->count() }}
                    </span>
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
                                {{ $booking->items->first()?->service?->name ?? '-' }}
                            </p>
                            <div class="pt-2 mt-2 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500">
                                <span>Stylist: {{ $booking->stylist?->name ?? 'Any Stylist' }}</span>
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
        <a href="{{ route('tablet.dashboard') }}" class="px-4 py-2 border border-stone-200 rounded-xl text-stone-650 hover:bg-stone-50 text-xs font-bold transition">
            Kembali Ke Menu
        </a>
    </div>
</div>

<script>
    // Automatic visual queue refresh every 10 seconds
    setTimeout(() => {
        window.location.reload();
    }, 10000);
</script>
@endsection
