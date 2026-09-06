@props(['stylists'])

<section id="stylists" class="py-16 md:py-20 bg-white border-b border-stone-100 font-sans">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-stone-200">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-[#c9512d] font-semibold block mb-1">
                    Collective Artists
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">
                    Hair Artists
                </h2>
                <p class="text-xs sm:text-sm text-stone-500 mt-1">
                    Tim hair artist profesional yang mendedikasikan presisi teknik dan pemahaman mendalam pada karakter rambut.
                </p>
            </div>
            <div>
                <a href="{{ route('stylists.index') }}" class="text-xs font-semibold text-[#c9512d] hover:text-[#b74423]">
                    Lihat Semua &rarr;
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach($stylists as $stylist)
                @php
                    $profileUrl = url('/' . ($stylist->slug ?: \Illuminate\Support\Str::slug($stylist->name)));
                @endphp
                <div class="border border-stone-200 rounded-3xl overflow-hidden flex flex-col justify-between hover:border-[#c9512d]/40 hover:shadow-md transition duration-200 bg-white group">
                    <div>
                        <!-- Stylist Photo -->
                        <a href="{{ $profileUrl }}" class="block relative aspect-square w-full overflow-hidden bg-stone-100">
                            <img src="{{ $stylist->display_photo }}" 
                                 alt="{{ $stylist->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-80"></div>
                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-md text-white text-[10px] font-bold uppercase tracking-wider border border-white/10">
                                    {{ $stylist->specialization ?: 'Hair Artist' }}
                                </span>
                            </div>
                            <div class="absolute bottom-3 left-4 right-4 text-white flex items-center justify-between">
                                <h3 class="text-lg font-bold text-white">
                                    {{ $stylist->name }}
                                </h3>
                                <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/20 backdrop-blur-md text-xs font-bold text-amber-300">
                                    <span>★</span>
                                    <span>{{ number_format($stylist->rating ?: 5.0, 1) }}</span>
                                </div>
                            </div>
                        </a>

                        <div class="p-5 space-y-2">
                            <p class="text-xs text-stone-500 leading-relaxed line-clamp-2">
                                {{ $stylist->bio ?? 'Mendedikasikan keahlian presisi dan pemahaman tekstur rambut alami untuk hasil terbaik.' }}
                            </p>
                        </div>
                    </div>

                    <div class="p-5 pt-0 flex items-center gap-2">
                        <a href="{{ $profileUrl }}" class="flex-1 py-2 rounded-xl border border-stone-200 hover:border-[#c9512d] hover:text-[#c9512d] text-center text-xs font-semibold text-stone-700 transition">
                            Lihat Profil
                        </a>
                        <a href="{{ route('booking.index', ['stylist_id' => $stylist->id]) }}" class="flex-1 py-2 rounded-xl bg-stone-900 hover:bg-[#c9512d] text-center text-xs font-semibold text-white transition">
                            Pilih Artist
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
