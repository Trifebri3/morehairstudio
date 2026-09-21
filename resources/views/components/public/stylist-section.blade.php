@props(['stylists'])

<style>
/* ── STYLIST SECTION FONTS ── */
.ss-badge  { font-family: 'Suisse Intl', sans-serif; font-size: 0.7rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600; }
.ss-h2     { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; }
.ss-h3     { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; }
.ss-role   { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.6rem; letter-spacing: 0.12em; text-transform: uppercase; }
.ss-sub    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
.ss-bio    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; }
.ss-btn    { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; }
</style>

<section id="stylists" class="py-16 md:py-20 bg-white border-b border-stone-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-stone-200">
            <div>
                <span class="ss-badge text-[#c9512d] block mb-2">Collective Artists</span>
                <h2 class="ss-h2 text-2xl sm:text-3xl text-stone-900">Hair Artists</h2>
                <p class="ss-sub text-xs sm:text-sm text-stone-500 mt-1">
                    Tim hair artist profesional yang mendedikasikan presisi teknik dan pemahaman mendalam pada karakter rambut.
                </p>
            </div>
            <a href="{{ route('stylists.index') }}" class="ss-btn text-xs text-[#c9512d] hover:text-[#b74423]">Lihat Semua &rarr;</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach($stylists as $stylist)
                @php
                    $profileUrl = url('/' . ($stylist->slug ?: \Illuminate\Support\Str::slug($stylist->name)));
                @endphp
                <div class="border border-stone-200 rounded-3xl overflow-hidden flex flex-col hover:border-[#c9512d]/40 hover:shadow-md transition duration-200 bg-white group">

                    <!-- Photo -->
                    <a href="{{ $profileUrl }}" class="block relative aspect-square w-full overflow-hidden bg-stone-100">
                        <img src="{{ $stylist->display_photo }}"
                             alt="{{ $stylist->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                        <!-- Role badge -->
                        <div class="absolute top-3 left-3">
                            <span class="ss-role px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-md text-white text-[10px] border border-white/10">
                                {{ $stylist->specialization ?: 'Hair Artist' }}
                            </span>
                        </div>

                        <!-- Name + Rating -->
                        <div class="absolute bottom-3 left-4 right-4 flex items-end justify-between">
                            <h3 class="ss-h3 text-white text-lg leading-tight">{{ $stylist->name }}</h3>
                            <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/20 backdrop-blur-md">
                                <span class="text-amber-300 text-xs">★</span>
                                <span class="ss-role text-white text-xs">{{ number_format($stylist->rating ?: 5.0, 1) }}</span>
                            </div>
                        </div>
                    </a>

                    <!-- Bio -->
                    <div class="p-5 flex-grow">
                        <p class="ss-bio text-xs text-stone-500 leading-relaxed line-clamp-2">
                            {{ $stylist->bio ?? 'Mendedikasikan keahlian presisi dan pemahaman tekstur rambut alami untuk hasil terbaik.' }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="p-5 pt-0 flex items-center gap-2">
                        <a href="{{ $profileUrl }}" class="ss-btn flex-1 py-2.5 rounded-xl border border-stone-200 hover:border-[#c9512d] hover:text-[#c9512d] text-center text-xs text-stone-700 transition uppercase tracking-wide">
                            Lihat Profil
                        </a>
                        <a href="{{ route('booking.index', ['stylist_id' => $stylist->id]) }}" class="ss-btn flex-1 py-2.5 rounded-xl bg-stone-900 hover:bg-[#c9512d] text-center text-xs text-white transition uppercase tracking-wide">
                            Pilih Artist
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</section>
