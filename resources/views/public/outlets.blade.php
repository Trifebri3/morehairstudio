<div class="py-24 max-w-5xl mx-auto px-4">
    <h1 class="text-4xl font-serif font-bold text-stone-900 mb-6">Our Studio Locations</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-12">
        @foreach($outlets as $outlet)
            <div class="bg-white border border-stone-200 shadow-sm p-8 rounded-2xl flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <a href="{{ route('outlets.show', $outlet->slug) }}" class="hover:text-amber-700 transition">
                        <h3 class="text-2xl font-bold font-serif text-stone-900 mb-2">{{ $outlet->name }}</h3>
                    </a>
                    <p class="text-stone-600 text-xs leading-relaxed mb-6">{{ $outlet->description }}</p>
                    
                    <div class="space-y-2 border-t border-stone-200 pt-4 text-xs text-stone-700">
                        <p><strong>Address:</strong> {{ $outlet->address }}</p>
                        <p><strong>Phone:</strong> {{ $outlet->phone }}</p>
                        <p><strong>WhatsApp:</strong> +{{ $outlet->whatsapp }}</p>
                    </div>
                    <div class="mt-6 rounded-xl overflow-hidden h-44 w-full border border-stone-200">
                        <iframe 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            src="https://maps.google.com/maps?q={{ urlencode($outlet->name . ' ' . $outlet->latitude . ',' . $outlet->longitude) }}&z=15&output=embed">
                        </iframe>
                    </div>
                </div>
                <div class="mt-8 pt-4 border-t border-stone-200 flex items-center justify-between text-[10px] text-stone-600 font-black">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $outlet->latitude }},{{ $outlet->longitude }}" target="_blank" class="text-stone-500 hover:text-stone-700 uppercase transition tracking-wider flex items-center gap-1">
                        View on Maps
                    </a>
                    <a href="{{ route('outlets.show', $outlet->slug) }}" class="text-[#0A3D91] hover:text-blue-800 uppercase transition tracking-wider">
                        Lihat Profil Detail &rarr;
                    </a>
                    <a href="{{ route('booking.index', ['outlet_id' => $outlet->id]) }}" class="text-amber-600 hover:text-amber-700 uppercase transition tracking-wider">Book Here &rarr;</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
