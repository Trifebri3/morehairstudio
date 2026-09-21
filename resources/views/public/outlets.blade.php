@extends('layouts.public')

@section('title', 'Studio & Lokasi Cabang | MORE Hair Studio Bandung')
@section('meta_description', 'Kunjungi studio MORE Hair Studio di Bandung. Informasi alamat, jadwal operasional cabang, dan reservasi langsung.')

@section('content')
<style>
/* ── OUTLETS PAGE FONTS ── */
.ou-badge   { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 400; }
.ou-h1      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.05; letter-spacing: -0.03em; }
.ou-sub     { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; line-height: 1.6; }
.ou-btn     { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; letter-spacing: 0.05em; }
.ou-h3      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; letter-spacing: -0.01em; }
.ou-desc    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; line-height: 1.6; }
.ou-label   { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.65rem; letter-spacing: 0.12em; text-transform: uppercase; font-weight: 400; }
.ou-val     { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; }
.ou-btn-alt { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 600; letter-spacing: 0.02em; }
.ou-small   { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
</style>

<div class="bg-white min-h-screen py-16 md:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <!-- Header -->
        <div class="border-b border-stone-200 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-2">
                <span class="ou-badge text-[#c9512d] block">
                    Studio &amp; Lokasi
                </span>
                <h1 class="ou-h1 text-3xl sm:text-4xl md:text-5xl text-stone-900">
                    Studio Kami
                </h1>
                <p class="ou-sub text-stone-600 text-xs sm:text-sm max-w-xl leading-relaxed">
                    Setiap ruang studio kami dirancang sebagai suaka urban yang menggabungkan kenyamanan modern dengan presisi potong rambut profesional.
                </p>
            </div>
            <div>
                <a href="{{ route('booking.index') }}" class="ou-btn inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs uppercase bg-[#c9512d] hover:bg-[#b74423] text-white transition duration-200 shadow-sm">
                    <span>Mulai Reservasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Outlets Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($outlets as $outlet)
                <div x-data="{ viewMode: 'photo' }" class="bg-white border border-stone-200 rounded-2xl overflow-hidden flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200 group">
                    
                    <!-- Media Area with Quick Photo/Map Switcher -->
                    <div class="relative aspect-16/9 bg-stone-100 overflow-hidden">
                        <div x-show="viewMode === 'photo'" class="w-full h-full">
                            <img src="/images/more_studio_interior.jpg" alt="{{ $outlet->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        </div>

                        <div x-show="viewMode === 'map'" class="w-full h-full bg-stone-200" x-cloak>
                            <iframe 
                                src="{{ $outlet->google_maps_embed_url }}" 
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Peta Titik {{ $outlet->name }}">
                            </iframe>
                        </div>

                        <!-- Switcher Pill Badge -->
                        <div class="absolute top-3 right-3 flex items-center bg-stone-900/80 backdrop-blur-md p-1 rounded-xl border border-white/10 shadow-lg text-[11px] font-semibold text-white z-10">
                            <button 
                                type="button" 
                                @click.prevent="viewMode = 'photo'" 
                                :class="viewMode === 'photo' ? 'bg-[#c9512d] text-white shadow-xs' : 'text-stone-300 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="ou-btn-alt">Foto</span>
                            </button>
                            <button 
                                type="button" 
                                @click.prevent="viewMode = 'map'" 
                                :class="viewMode === 'map' ? 'bg-[#c9512d] text-white shadow-xs' : 'text-stone-300 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#c9512d]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                <span class="ou-btn-alt">Titik Maps</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 sm:p-7 space-y-4 flex-grow flex flex-col justify-between">
                        <div class="space-y-3">
                            <h3 class="ou-h3 text-xl text-stone-900">{{ $outlet->name }}</h3>
                            <p class="ou-desc text-xs sm:text-sm text-stone-600 leading-relaxed">{{ $outlet->description }}</p>
                            
                            <div class="space-y-2 border-t border-stone-100 pt-4 text-xs text-stone-600">
                                <div class="flex items-start gap-3">
                                    <span class="ou-label text-stone-400 min-w-[60px] pt-0.5">Alamat</span>
                                    <span class="ou-val text-stone-800">{{ $outlet->address }}</span>
                                </div>
                                @if($outlet->phone)
                                    <div class="flex items-center gap-3">
                                        <span class="ou-label text-stone-400 min-w-[60px]">Telepon</span>
                                        <span class="ou-val text-stone-800">{{ $outlet->phone }}</span>
                                    </div>
                                @endif
                                @if($outlet->whatsapp)
                                    <div class="flex items-center gap-3">
                                        <span class="ou-label text-stone-400 min-w-[60px]">WhatsApp</span>
                                        <span class="ou-val text-stone-800">+{{ $outlet->whatsapp }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Dedicated Titik Maps & Rute Google Maps -->
                            <div class="border-t border-stone-100 pt-4 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#c9512d] animate-pulse"></span>
                                        <span class="ou-label text-stone-900 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-[#c9512d]" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            Titik Lokasi Maps
                                        </span>
                                    </div>
                                    <a href="{{ $outlet->google_maps_url }}" target="_blank" rel="noopener noreferrer" class="ou-btn-alt inline-flex items-center gap-1 text-[11px] text-[#c9512d] hover:text-[#b74423] transition group/mapbtn">
                                        <span>Buka Google Maps</span>
                                        <svg class="w-3 h-3 group-hover/mapbtn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>

                                <!-- Live Interactive Mini Map -->
                                <div class="rounded-xl overflow-hidden border border-stone-200 h-44 sm:h-48 w-full bg-stone-100 relative shadow-sm">
                                    <iframe 
                                        src="{{ $outlet->google_maps_embed_url }}" 
                                        width="100%" 
                                        height="100%" 
                                        style="border:0;" 
                                        allowfullscreen="" 
                                        loading="lazy" 
                                        referrerpolicy="no-referrer-when-downgrade"
                                        title="Peta Titik Lokasi {{ $outlet->name }}">
                                    </iframe>
                                </div>

                                <div class="flex items-center justify-between text-[10px] text-stone-500 pt-0.5">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="ou-small">Titik Pin:</span> <span class="ou-label text-stone-700">{{ $outlet->latitude ?? '-6.911558' }}, {{ $outlet->longitude ?? '107.623485' }}</span>
                                    </span>
                                    <a href="{{ $outlet->google_maps_url }}" target="_blank" rel="noopener noreferrer" class="ou-small text-stone-500 hover:text-[#c9512d] underline">
                                        Petunjuk Arah Rute &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="pt-5 border-t border-stone-100 flex items-center justify-between gap-4">
                            <a href="{{ route('outlets.show', $outlet->slug) }}" class="ou-btn-alt text-xs text-stone-700 hover:text-[#c9512d] transition">
                                Detail &amp; Rute &rarr;
                            </a>
                            <a href="{{ route('booking.index', ['outlet_id' => $outlet->id]) }}" class="ou-btn inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs bg-[#c9512d] hover:bg-[#b74423] text-white transition duration-200">
                                <span>Pilih Studio Ini</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
