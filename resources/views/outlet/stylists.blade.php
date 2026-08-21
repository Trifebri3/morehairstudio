@extends('layouts.admin')

@section('page_title')
    Outlet Stylists Directory
@endsection

@section('content')
<div class="space-y-6">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
        <form method="GET" action="{{ route('outlet.stylists') }}" class="w-full md:max-w-xs">
            <x-ui.input placeholder="Search stylists by name..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($stylists as $stylist)
                <div class="border border-stone-200 rounded-2xl p-5 bg-white shadow-sm flex flex-col justify-between space-y-4 hover:border-[#0A3D91] transition duration-200">
                    <div class="flex items-start space-x-4">
                        <div class="h-12 w-12 rounded-xl bg-stone-100 text-stone-500 font-extrabold text-sm flex items-center justify-center border border-stone-200 font-mono">
                            {{ collect(explode(' ', $stylist->name))->map(fn($n) => substr($n, 0, 1))->join('') }}
                        </div>
                        <div>
                            <h4 class="font-bold text-stone-900 text-sm uppercase tracking-tight">{{ $stylist->name }}</h4>
                            <span class="text-[9px] text-[#0A3D91] uppercase font-extrabold tracking-wider block mt-0.5">{{ $stylist->specialization ?? 'General Stylist' }}</span>
                            <span class="text-[10px] text-amber-600 font-bold block mt-1">★ {{ number_format($stylist->rating, 1) }} / 5.0</span>
                        </div>
                    </div>

                    <p class="text-stone-500 text-[11px] leading-relaxed italic truncate-2-lines">
                        {{ $stylist->bio ?? 'No bio provided.' }}
                    </p>

                    <div class="pt-4 border-t border-stone-100 flex justify-between items-center">
                        @if(in_array($stylist->status, ['pending_active', 'pending_inactive', 'pending_leave']))
                            <div class="flex flex-col space-y-2 w-full">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-amber-600 font-sans">
                                        {{ $stylist->status === 'pending_active' ? 'Minta Aktif' : 'Minta Cuti' }}
                                    </span>
                                    <x-ui.badge variant="warning">Pending</x-ui.badge>
                                </div>
                                <div class="flex space-x-2 pt-1">
                                    <form method="POST" action="{{ route('outlet.stylists.approve', $stylist->id) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full text-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] py-1.5 font-bold uppercase rounded-lg transition">
                                            Setuju
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('outlet.stylists.reject', $stylist->id) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full text-center justify-center bg-rose-600 hover:bg-rose-700 text-white text-[10px] py-1.5 font-bold uppercase rounded-lg transition">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <x-ui.badge variant="{{ $stylist->status === 'active' ? 'success' : 'neutral' }}">
                                {{ $stylist->status === 'active' ? 'Active' : 'Inactive / Cuti' }}
                            </x-ui.badge>
                            
                            <form method="POST" action="{{ route('outlet.stylists.toggle', $stylist->id) }}">
                                @csrf
                                <x-ui.button variant="outline" size="sm" type="submit">
                                    Toggle Status
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-stone-400">
                    No stylists found matching query.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
