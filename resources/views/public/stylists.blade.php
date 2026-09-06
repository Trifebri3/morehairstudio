@extends('layouts.public')

@section('title', 'Hair Artists & Stylists | MORE Hair Studio Bandung')
@section('meta_description', 'Temui para Hair Artist di MORE Hair Studio Bandung. Berpengalaman dalam teknik potong presisi dan chemical texture treatment. Lihat profil dan pesan jadwal langsung.')

@section('content')
<div class="bg-[#fafaf9] min-h-screen py-16 md:py-24 font-sans text-stone-900" x-data="{ copiedSlug: null }">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <!-- Header -->
        <div class="border-b border-stone-200 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-2">
                <span class="text-xs uppercase tracking-wider text-[#c9512d] font-semibold block">
                    Hair Artists &amp; Stylists Collective
                </span>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-stone-900 tracking-tight">
                    Seniman Rambut Kami
                </h1>
                <p class="text-stone-600 text-xs sm:text-sm max-w-xl font-normal leading-relaxed">
                    Bukan sekadar potong rambut biasa—tim hair artist kami memahami proporsi wajah, arah tumbuh, dan tekstur rambut secara mendalam. Klik profil untuk melihat detail portofolio dan jadwal kerja.
                </p>
            </div>
            <div>
                <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-semibold uppercase tracking-wider bg-[#c9512d] hover:bg-[#b74423] text-white transition duration-200 shadow-sm">
                    <span>Pesan Jadwal</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Stylists Grid with Photos & Profile Links -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($stylists as $stylist)
                @php
                    $profileUrl = url('/' . ($stylist->slug ?: \Illuminate\Support\Str::slug($stylist->name)));
                @endphp
                <div class="bg-white border border-stone-200 rounded-3xl overflow-hidden flex flex-col justify-between hover:border-[#c9512d]/50 hover:shadow-md transition duration-200 group">
                    
                    <div>
                        <!-- Stylist Photo & Badges -->
                        <a href="{{ $profileUrl }}" class="block relative aspect-square w-full overflow-hidden bg-stone-100">
                            <img src="{{ $stylist->display_photo }}" 
                                 alt="{{ $stylist->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            
                            <!-- Overlay gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>

                            <!-- Top Badges -->
                            <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-md text-white text-[10px] font-bold uppercase tracking-wider border border-white/10">
                                    {{ $stylist->specialization ?? 'Hair Artist' }}
                                </span>
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/90 text-white text-[10px] font-bold flex items-center gap-1 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                    Aktif
                                </span>
                            </div>

                            <!-- Bottom Photo Details: Name & Rating -->
                            <div class="absolute bottom-3 left-4 right-4 text-white">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-bold tracking-tight text-white group-hover:text-[#faede7] transition-colors">
                                        {{ $stylist->name }}
                                    </h3>
                                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/20 backdrop-blur-md text-xs font-bold text-amber-300">
                                        <span>★</span>
                                        <span>{{ number_format($stylist->rating ?: 5.0, 1) }}</span>
                                    </div>
                                </div>
                                @if($stylist->instagram)
                                    <span class="text-[11px] text-stone-300 font-mono block mt-0.5">
                                        {{ $stylist->instagram }}
                                    </span>
                                @endif
                            </div>
                        </a>

                        <!-- Body Content -->
                        <div class="p-6 space-y-4">
                            <p class="text-xs text-stone-600 leading-relaxed font-normal line-clamp-3">
                                {{ $stylist->bio ?? 'Mendedikasikan keahlian presisi dan pemahaman tekstur rambut alami untuk hasil terbaik.' }}
                            </p>

                            <div class="flex items-center justify-between text-xs text-stone-500 pt-2 border-t border-stone-100">
                                <span>Studio:</span>
                                <strong class="text-stone-800 font-semibold">{{ $stylist->outlet->name ?? 'MORE Hair Studio' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions Footer: Detail & Booking -->
                    <div class="px-6 pb-6 pt-2 border-t border-stone-100 flex items-center gap-3">
                        <a href="{{ $profileUrl }}" 
                           class="flex-1 py-2.5 px-3 rounded-xl border border-stone-200 hover:border-[#c9512d] hover:bg-[#faede7]/30 text-stone-700 hover:text-[#c9512d] text-center text-xs font-bold transition">
                            Lihat Profil
                        </a>
                        <a href="{{ route('booking.index', ['stylist_id' => $stylist->id]) }}" 
                           class="flex-1 py-2.5 px-3 rounded-xl bg-stone-900 hover:bg-[#c9512d] text-white text-center text-xs font-bold transition shadow-sm">
                            Pilih Artist
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
