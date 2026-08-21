@extends('layouts.tablet')

@section('content')
<div class="h-full flex flex-col justify-between py-4">
    <div class="space-y-6">
        <div>
            <h3 class="text-xl font-bold text-stone-900 mb-2">Stylist Attendance</h3>
            <p class="text-stone-500 text-xs">Ketuk tombol Clock In atau Clock Out sesuai nama Anda untuk merekam kehadiran hari ini.</p>
        </div>

        <!-- Success / Error Alerts -->
        @if(session()->has('message'))
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        @endif

        @if(session()->has('error'))
            <x-ui.alert variant="danger">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <!-- Stylist Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            @foreach($stylists as $stylist)
                @php
                    $att = $attendances->get($stylist->id);
                @endphp
                <div class="glass-panel p-6 rounded-2xl flex items-center justify-between border-stone-250 bg-white shadow-sm">
                    <div class="flex items-center space-x-4">
                        <div class="h-14 w-14 bg-stone-100 rounded-full flex items-center justify-center border border-stone-200 text-[#0A3D91] font-black text-sm uppercase">
                            {{ substr($stylist->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-stone-900">{{ $stylist->name }}</h4>
                            <span class="text-xxs text-stone-400 uppercase tracking-wide block mb-1">
                                {{ $stylist->specialization }}
                            </span>
                            
                            <!-- Attendance Status badge -->
                            @if(!$att)
                                <x-ui.badge variant="neutral">Not Checked In</x-ui.badge>
                            @elseif($att && !$att->clock_out)
                                <x-ui.badge variant="{{ $att->status === 'late' ? 'warning' : 'success' }}">
                                    Clocked In @ {{ \Carbon\Carbon::parse($att->clock_in)->format('H:i') }}
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">
                                    Clocked Out @ {{ \Carbon\Carbon::parse($att->clock_out)->format('H:i') }}
                                </x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <!-- Absen Action Buttons -->
                    <div class="flex space-x-2">
                        @if(!$att)
                            <form method="POST" action="{{ route('tablet.attendance.clock-in', $stylist->id) }}">
                                @csrf
                                <x-ui.button variant="primary" size="sm" type="submit">
                                    Clock In
                                </x-ui.button>
                            </form>
                        @elseif($att && !$att->clock_out)
                            <form method="POST" action="{{ route('tablet.attendance.clock-out', $stylist->id) }}">
                                @csrf
                                <x-ui.button variant="danger" size="sm" type="submit">
                                    Clock Out
                                </x-ui.button>
                            </form>
                        @else
                            <span class="text-stone-400 text-xs font-bold uppercase tracking-wider py-2 px-4 bg-stone-100 rounded-full border border-stone-200">
                                Selesai
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="pt-6 border-t border-stone-200 flex justify-end">
        <a href="{{ route('tablet.dashboard') }}" class="px-4 py-2 border border-stone-200 rounded-xl text-stone-650 hover:bg-stone-50 text-xs font-bold transition">
            Kembali
        </a>
    </div>
</div>
@endsection
