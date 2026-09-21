@props(['outlets'])

<style>
/* ── OUTLET SECTION FONTS ── */
.os-badge { font-family: 'Suisse Intl', sans-serif; font-size: 0.7rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600; }
.os-h2    { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; }
.os-h3    { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; }
.os-sub   { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
.os-body  { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; }
.os-label { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.6rem; letter-spacing: 0.12em; text-transform: uppercase; font-weight: 400; }
.os-link  { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; }
</style>

<section id="outlets" class="py-16 md:py-24 bg-white border-b border-stone-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-stone-100">
            <div>
                <span class="os-badge text-[#c9512d] block mb-2">Studio Kami</span>
                <h2 class="os-h2 text-2xl sm:text-3xl text-stone-900">Studio &amp; Cabang</h2>
                <p class="os-sub text-xs sm:text-sm text-stone-500 mt-1 max-w-xl">
                    Jadwal, hair artist, dan slot layanan dikelola per studio. Pilih studio terdekat untuk reservasi langsung.
                </p>
            </div>
            <a href="{{ route('outlets.index') }}" class="os-link text-xs text-[#c9512d] hover:text-[#b74423]">Semua Lokasi &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($outlets as $outlet)
                <div class="border border-stone-200 rounded-2xl overflow-hidden flex flex-col hover:border-[#c9512d]/40 hover:shadow-md transition duration-200 bg-white">

                    <div class="aspect-video bg-stone-100 relative overflow-hidden">
                        <img src="/images/more_studio_interior.jpg" alt="{{ $outlet->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-4 left-4">
                            <h3 class="os-h3 text-white text-xl">{{ $outlet->name }}</h3>
                        </div>
                    </div>

                    <div class="p-6 space-y-4 flex-grow flex flex-col justify-between">
                        <div class="space-y-3">
                            <p class="os-body text-xs text-stone-600 leading-relaxed">{{ $outlet->description }}</p>

                            <div class="pt-3 space-y-2 border-t border-stone-100">
                                <div class="flex items-start gap-3">
                                    <span class="os-label text-stone-400 min-w-[55px]">Alamat</span>
                                    <span class="os-body text-xs text-stone-800">{{ $outlet->address }}</span>
                                </div>
                                @if($outlet->whatsapp || $outlet->phone)
                                <div class="flex items-center gap-3">
                                    <span class="os-label text-stone-400 min-w-[55px]">Kontak</span>
                                    <span class="os-h3 text-xs text-stone-800">{{ $outlet->whatsapp ? '+'.$outlet->whatsapp : $outlet->phone }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 mt-2 border-t border-stone-100 flex items-center justify-between gap-3">
                            <a href="{{ route('outlets.show', $outlet->slug) }}" class="os-link text-xs text-stone-600 hover:text-[#c9512d] transition">
                                Detail &amp; Rute &rarr;
                            </a>
                            <a href="{{ route('booking.index', ['outlet_id' => $outlet->id]) }}"
                               class="os-link inline-flex items-center gap-1 px-5 py-2.5 bg-[#c9512d] hover:bg-[#b74423] text-white text-xs uppercase tracking-wide rounded-xl transition duration-200">
                                Pilih Studio Ini
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</section>
