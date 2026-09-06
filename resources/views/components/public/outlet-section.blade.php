@props(['outlets'])

<section id="outlets" class="py-16 md:py-24 bg-white border-b border-stone-100 font-sans">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-stone-200">
            <div>
                <span class="text-xs uppercase tracking-wider text-[#c9512d] font-semibold block mb-1">
                    Lokasi Studio
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">
                    Studio &amp; Cabang Kami
                </h2>
                <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-xl">
                    Jadwal operasional, hair artist, dan slot layanan dikelola secara spesifik di setiap studio. Pilih studio yang Anda tuju untuk reservasi langsung.
                </p>
            </div>
            <div>
                <a href="{{ route('outlets.index') }}" class="text-xs font-semibold text-[#c9512d] hover:text-[#b74423]">
                    Semua Lokasi &rarr;
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($outlets as $outlet)
                <div class="border border-stone-200 rounded-2xl overflow-hidden flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200 bg-white">
                    
                    <div class="aspect-16/9 bg-stone-100 relative overflow-hidden">
                        <img src="/images/more_studio_interior.jpg" alt="{{ $outlet->name }}" class="w-full h-full object-cover">
                    </div>

                    <div class="p-6 space-y-4 flex-grow flex flex-col justify-between">
                        <div class="space-y-2">
                            <h3 class="text-xl font-bold text-stone-900">{{ $outlet->name }}</h3>
                            <p class="text-xs text-stone-600 leading-relaxed font-normal">
                                {{ $outlet->description }}
                            </p>

                            <div class="pt-2 space-y-1.5 text-xs text-stone-500 border-t border-stone-100">
                                <div class="flex items-start gap-2">
                                    <span class="text-stone-400 font-medium min-w-[60px]">Alamat:</span>
                                    <span class="text-stone-800">{{ $outlet->address }}</span>
                                </div>
                                @if($outlet->whatsapp || $outlet->phone)
                                    <div class="flex items-center gap-2">
                                        <span class="text-stone-400 font-medium min-w-[60px]">Kontak:</span>
                                        <span class="text-stone-800">{{ $outlet->whatsapp ? '+'.$outlet->whatsapp : $outlet->phone }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-stone-100 flex items-center justify-between gap-4">
                            <a href="{{ route('outlets.show', $outlet->slug) }}" class="text-xs font-semibold text-stone-700 hover:text-[#c9512d] transition">
                                Detail &amp; Rute &rarr;
                            </a>
                            <a href="{{ route('booking.index', ['outlet_id' => $outlet->id]) }}" class="inline-flex items-center gap-1 px-4 py-2 bg-[#c9512d] hover:bg-[#b74423] text-white text-xs font-semibold rounded-xl transition duration-200 shadow-2xs">
                                <span>Pilih Studio Ini</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</section>
