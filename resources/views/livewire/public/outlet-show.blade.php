<div class="bg-stone-50 min-h-screen font-sans">
    <!-- Hero Banner & Header -->
    <div class="relative bg-gradient-to-r from-[#020617] via-[#0b1329] to-[#0A3D91] text-white py-24 px-6 md:px-12 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.05)_0%,transparent_80%)]"></div>
        <div class="max-w-6xl mx-auto relative z-10 space-y-6">
            <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-amber-400/10 px-3 py-1.5 rounded-full border border-amber-400/20">Studio Profile</span>
            <h1 class="text-4xl md:text-6xl font-black uppercase tracking-tight leading-none bg-gradient-to-r from-white via-stone-100 to-blue-200 bg-clip-text text-transparent">
                {{ $outlet->name }}
            </h1>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-white/10 text-xs md:text-sm text-stone-300">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    <span><strong>Alamat:</strong><br>{{ $outlet->address }}</span>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span><strong>Telepon / WhatsApp:</strong><br>{{ $outlet->phone }} / +{{ $outlet->whatsapp }}</span>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><strong>Jam Operasional:</strong><br>
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
                            Setiap Hari: 09:00 - 21:00
                        @endif
                    </span>
                </div>
            </div>

            <div class="pt-6">
                <a href="{{ route('booking.index', ['outlet_id' => $outlet->id]) }}" class="inline-flex items-center justify-center px-8 py-4 bg-amber-500 text-stone-950 font-black uppercase tracking-wider rounded-xl hover:bg-amber-400 transition shadow-lg shadow-amber-500/20 transform hover:-translate-y-0.5 duration-200 text-xs">
                    Booking Di Studio Ini &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-6xl mx-auto px-6 py-16 grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Left Side: Profile, Gallery, Map -->
        <div class="lg:col-span-2 space-y-12">
            <!-- About Us -->
            <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200/80 shadow-sm space-y-4">
                <h2 class="text-xl font-bold uppercase tracking-tight text-stone-900 border-b pb-3">Tentang Kami</h2>
                <p class="text-stone-600 text-sm leading-relaxed font-light">
                    {{ $outlet->description ?: 'MORE Hair Studio berkomitmen untuk menyajikan pengalaman perawatan rambut premium yang dirancang secara khusus untuk kenyamanan dan gaya personal Anda.' }}
                </p>
            </div>

            <!-- Gallery -->
            <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200/80 shadow-sm space-y-6">
                <h2 class="text-xl font-bold uppercase tracking-tight text-stone-900 border-b pb-3">Galeri Lokasi</h2>
                
                @php
                    $galleryImages = is_array($outlet->gallery) ? $outlet->gallery : [];
                @endphp

                @if(count($galleryImages) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($galleryImages as $img)
                            <div class="rounded-2xl overflow-hidden h-48 border border-stone-200/50 shadow-inner group">
                                <img src="{{ $img }}" alt="Gallery Image" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Fallback placeholder styling -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-2xl overflow-hidden h-48 border border-stone-200/50 shadow-inner bg-gradient-to-br from-stone-100 to-stone-200 flex items-center justify-center">
                            <span class="text-xxs uppercase tracking-widest text-stone-400 font-bold font-mono">Modern Salon Interior</span>
                        </div>
                        <div class="rounded-2xl overflow-hidden h-48 border border-stone-200/50 shadow-inner bg-gradient-to-br from-stone-100 to-stone-200 flex items-center justify-center">
                            <span class="text-xxs uppercase tracking-widest text-stone-400 font-bold font-mono">Premium Styling Chairs</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Hair Stylists working here -->
            <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200/80 shadow-sm space-y-6">
                <h2 class="text-xl font-bold uppercase tracking-tight text-stone-900 border-b pb-3">Hairstylists Terpercaya</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @forelse($stylists as $s)
                        <div class="border border-stone-150 rounded-2xl p-6 bg-stone-50/30 flex items-center space-x-4 hover:shadow-sm transition">
                            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-stone-200 bg-white">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ $s->slug }}" alt="{{ $s->name }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h4 class="font-extrabold text-stone-900 text-sm uppercase tracking-tight">{{ $s->name }}</h4>
                                <p class="text-[10px] text-stone-400 uppercase font-black tracking-wider block mt-0.5">{{ $s->specialization }}</p>
                                <div class="flex items-center space-x-1 mt-2">
                                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span class="text-xs font-bold text-stone-700">4.9 / 5.0 Rating</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-stone-400 text-xs py-4">Belum ada stylist aktif terdaftar.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Side: Service Menu List & Map Embed -->
        <div class="space-y-12">
            <!-- Service List Menu -->
            <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200/80 shadow-sm space-y-6">
                <h2 class="text-xl font-bold uppercase tracking-tight text-stone-900 border-b pb-3">Daftar Layanan</h2>
                
                @forelse($servicesByCategory as $category => $items)
                    <div class="space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-widest text-[#0A3D91] bg-blue-50/60 px-3 py-1.5 rounded-lg border border-blue-100/50 block w-max">
                            {{ $category }}
                        </h3>
                        <div class="space-y-4 border-l border-stone-200 pl-4 py-2">
                            @foreach($items as $s)
                                <div class="flex justify-between items-start">
                                    <div class="space-y-0.5">
                                        <h4 class="text-xs font-bold text-stone-900 uppercase">{{ $s['name'] }}</h4>
                                        <span class="text-[9px] font-extrabold uppercase text-stone-400 block tracking-wider">{{ $s['duration'] }} Menit</span>
                                    </div>
                                    <span class="font-mono text-xs font-black text-stone-850">
                                        Rp {{ number_format($s['price'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-stone-400 text-xs py-4">Belum ada layanan aktif di studio ini.</p>
                @endforelse
            </div>

            <!-- Map View Section -->
            <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200/80 shadow-sm space-y-4">
                <h2 class="text-xl font-bold uppercase tracking-tight text-stone-900 border-b pb-3">Peta Lokasi</h2>
                
                <div class="rounded-2xl overflow-hidden h-64 border border-stone-200 shadow-inner">
                    @if($outlet->map_iframe)
                        {!! $outlet->map_iframe !!}
                    @else
                        <iframe 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            src="https://maps.google.com/maps?q={{ urlencode($outlet->name . ' ' . $outlet->latitude . ',' . $outlet->longitude) }}&z=15&output=embed">
                        </iframe>
                    @endif
                </div>
                
                <div class="pt-2">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $outlet->latitude }},{{ $outlet->longitude }}" target="_blank" class="w-full inline-flex items-center justify-center py-3 border border-stone-200 text-xxs font-black uppercase tracking-wider text-stone-600 rounded-xl hover:bg-stone-50 transition">
                        Buka di Google Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
