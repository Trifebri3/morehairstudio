@extends('layouts.public')

@section('title', $outlet->name . ' • Studio Lounge Profile | MORE Hair Studio')
@section('meta_description', 'Profil lengkap studio lounge ' . $outlet->name . ' di Bandung. Alamat, fasilitas, master hair artist yang bertugas, dan daftar layanan reservasi.')

@section('content')
<div class="bg-white min-h-screen font-sans">
    <!-- Clean White Dominated Header with Burnt Orange Accent Line -->
    <div class="border-b border-stone-200 bg-white py-14 sm:py-20 px-4 sm:px-6 md:px-12 relative">
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#c9512d] bg-[#faede7] px-3 py-1 rounded-full">
                <span class="w-2 h-2 rounded-full bg-[#c9512d]"></span>
                <span>Studio Resmi MORE</span>
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-6xl font-black font-primary uppercase tracking-tight text-stone-900 leading-none">
                {{ $outlet->name }}
            </h1>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-stone-200 text-xs sm:text-sm text-stone-600 font-secondary">
                <div class="space-y-1">
                    <span class="font-bold uppercase text-[10px] tracking-wider text-[#c9512d] block font-mono">Alamat Studio</span>
                    <span class="text-stone-800">{{ $outlet->address }}</span>
                </div>
                <div class="space-y-1">
                    <span class="font-bold uppercase text-[10px] tracking-wider text-[#c9512d] block font-mono">Kontak Reservasi</span>
                    <span class="text-stone-800 font-mono">{{ $outlet->phone }} / +{{ $outlet->whatsapp }}</span>
                </div>
                <div class="space-y-1">
                    <span class="font-bold uppercase text-[10px] tracking-wider text-[#c9512d] block font-mono">Jam Operasional</span>
                    <span class="text-stone-800 font-mono">
                        @if(is_array($outlet->opening_hours))
                            @foreach($outlet->opening_hours as $day => $hours)
                                <span class="block text-xxs capitalize">
                                    {{ $day }}: 
                                    @if(is_array($hours))
                                        {{ $hours['open'] ?? '' }} - {{ $hours['close'] ?? '' }}
                                    @else
                                        {{ $hours }}
                                    @endif
                                </span>
                            @endforeach
                        @else
                            Setiap Hari: 09:00 - 21:00 WIB
                        @endif
                    </span>
                </div>
            </div>

            <div class="pt-4">
                <a href="{{ route('booking.index', ['outlet_id' => $outlet->id]) }}" class="inline-flex items-center justify-center px-8 py-4 bg-[#c9512d] hover:bg-[#b74423] text-white font-mono font-bold uppercase tracking-wider rounded-xl transition duration-200 shadow-sm hover:shadow-md hover:shadow-[#c9512d]/20 text-xs">
                    Booking Di Studio Ini &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Left Side: Profile, Gallery, Stylists -->
        <div class="lg:col-span-2 space-y-12">
            <!-- About Us -->
            <div class="p-8 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-4">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-[#c9512d] block">(01) Filosofi Studio</span>
                <h2 class="text-xl font-bold uppercase tracking-tight font-primary text-stone-900 border-b border-stone-100 pb-3">Tentang Studio Ini</h2>
                <p class="text-stone-600 text-xs sm:text-sm leading-relaxed font-light font-secondary">
                    {{ $outlet->description ?: 'MORE Hair Studio berkomitmen untuk menyajikan pengalaman perawatan rambut premium yang dirancang secara khusus untuk kenyamanan dan keotentikan gaya personal Anda.' }}
                </p>
            </div>

            <!-- Gallery -->
            <div class="p-8 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-6">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-[#c9512d] block">(02) Tata Ruang &amp; Atmosfer</span>
                <h2 class="text-xl font-bold uppercase tracking-tight font-primary text-stone-900 border-b border-stone-100 pb-3">Galeri Studio</h2>
                
                @php
                    $galleryImages = is_array($outlet->gallery) ? $outlet->gallery : [];
                @endphp

                @if(count($galleryImages) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($galleryImages as $img)
                            <div class="rounded-2xl overflow-hidden h-48 border border-stone-200 group">
                                <img src="{{ $img }}" alt="Gallery Image" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-2xl overflow-hidden h-48 border border-stone-200 bg-stone-50 flex flex-col items-center justify-center p-4 text-center">
                            <span class="text-xs uppercase tracking-widest text-[#c9512d] font-bold font-mono">Precision Styling Chairs</span>
                            <span class="text-[10px] text-stone-400 mt-1">Interior Calibrated Lighting</span>
                        </div>
                        <div class="rounded-2xl overflow-hidden h-48 border border-stone-200 bg-stone-50 flex flex-col items-center justify-center p-4 text-center">
                            <span class="text-xs uppercase tracking-widest text-[#c9512d] font-bold font-mono">Modern Coffee Lounge</span>
                            <span class="text-[10px] text-stone-400 mt-1">Human-Hair Centered Ambience</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Hair Stylists working here -->
            <div class="p-8 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-6">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-[#c9512d] block">(03) Hair Artists</span>
                <h2 class="text-xl font-bold uppercase tracking-tight font-primary text-stone-900 border-b border-stone-100 pb-3">Hair Artists di Studio Ini</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @forelse($stylists as $s)
                        <div class="border border-stone-200 rounded-2xl p-6 bg-stone-50/50 flex items-center space-x-4 hover:border-[#c9512d]/40 transition">
                            <div class="w-16 h-16 rounded-full overflow-hidden border border-stone-200 bg-white flex items-center justify-center font-primary font-bold text-stone-400 text-xl shrink-0">
                                {{ collect(explode(' ', $s->name))->map(fn($n) => substr($n, 0, 1))->join('') }}
                            </div>
                            <div>
                                <h4 class="font-bold text-stone-900 text-sm font-primary uppercase tracking-tight">{{ $s->name }}</h4>
                                <p class="text-[10px] text-[#c9512d] uppercase font-bold tracking-wider block mt-0.5 font-mono">{{ $s->specialization }}</p>
                                <div class="flex items-center space-x-1 mt-2 text-xs font-mono font-bold text-stone-700">
                                    <svg class="w-3 h-3 text-[#c9512d] fill-current inline" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span>{{ number_format($s->rating, 1) }} Rating</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-stone-400 text-xs py-4 font-mono">Belum ada stylist aktif terdaftar di studio ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Side: Service Menu List & Map Embed -->
        <div class="space-y-12">
            <!-- Service List Menu -->
            <div class="p-8 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-6">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-[#c9512d] block">Menu Layanan</span>
                <h2 class="text-xl font-bold uppercase tracking-tight font-primary text-stone-900 border-b border-stone-100 pb-3">Daftar Treatment</h2>
                
                @forelse($servicesByCategory as $category => $items)
                    <div class="space-y-3">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-[#c9512d] bg-[#faede7] px-3 py-1 rounded-md border border-[#c9512d]/20 inline-block font-mono">
                            {{ $category }}
                        </h3>
                        <div class="space-y-3 border-l-2 border-stone-200 pl-4 py-1 font-secondary">
                            @foreach($items as $s)
                                <div class="flex justify-between items-start text-xs">
                                    <div class="space-y-0.5">
                                        <h4 class="font-bold text-stone-900 uppercase font-primary">{{ $s['name'] }}</h4>
                                        <span class="text-[10px] font-mono text-stone-400 block">{{ $s['duration'] }} Menit</span>
                                    </div>
                                    <span class="font-mono text-xs font-bold text-[#c9512d]">
                                        Rp {{ number_format($s['price'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-stone-400 text-xs py-4 font-mono">Belum ada layanan aktif di studio ini.</p>
                @endforelse

                <div class="pt-2">
                    <a href="{{ route('booking.index', ['outlet_id' => $outlet->id]) }}" class="w-full inline-flex items-center justify-center py-3 bg-[#c9512d] hover:bg-[#b74423] text-white font-mono font-bold uppercase tracking-wider rounded-xl transition text-xs shadow-2xs">
                        Pesan Sekarang &rarr;
                    </a>
                </div>
            </div>

            <!-- Map View Section -->
            <div class="p-8 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-[#c9512d] block">Lokasi &amp; Navigasi</span>
                    <span class="text-[11px] font-mono text-stone-500">{{ $outlet->latitude ?? '-6.911558' }}, {{ $outlet->longitude ?? '107.623485' }}</span>
                </div>
                <h2 class="text-xl font-bold uppercase tracking-tight font-primary text-stone-900 border-b border-stone-100 pb-3">Titik Maps &amp; Peta Interaktif</h2>
                
                <div class="rounded-2xl overflow-hidden h-64 border border-stone-200 shadow-2xs">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        src="{{ $outlet->google_maps_embed_url }}"
                        title="Peta Lokasi {{ $outlet->name }}">
                    </iframe>
                </div>
                
                <div class="pt-2">
                    <a href="{{ $outlet->google_maps_url }}" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center py-3 border border-stone-200 text-xs font-mono font-bold uppercase tracking-wider text-stone-700 hover:text-[#c9512d] hover:border-[#c9512d] rounded-xl hover:bg-stone-50 transition gap-1.5">
                        <span>Buka di Google Maps</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
