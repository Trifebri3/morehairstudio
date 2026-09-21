@props([
    'services' => null
])

<style>
/* ── PRICE LIST FONTS ── */
.pl-badge    { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 400; }
.pl-h2       { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.05; letter-spacing: -0.03em; }
.pl-sub      { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; line-height: 1.6; }
.pl-cat      { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 400; }
.pl-svc-name { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; letter-spacing: -0.01em; }
.pl-price    { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; }
.pl-dur      { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.62rem; letter-spacing: 0.1em; }
.pl-desc     { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; line-height: 1.6; }
.pl-addon    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; }
.pl-addon-val{ font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 600; }
.pl-btn      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; letter-spacing: 0.05em; }
</style>

<section id="pricelist" class="py-16 md:py-20 bg-white border-b border-stone-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-stone-200">
            <div class="space-y-2">
                <span class="pl-badge text-[#c9512d] block">Price List &bull; Daftar Layanan</span>
                <h2 class="pl-h2 text-3xl sm:text-4xl md:text-5xl text-stone-900">
                    Layanan &amp; Tarif
                </h2>
                <p class="pl-sub text-sm text-stone-500 max-w-lg">
                    Harga transparan tanpa biaya tersembunyi. Layanan potong rambut sudah termasuk 10-menit Define Session, hair wash, dan styling.
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('booking.index') }}"
                   class="pl-btn inline-flex items-center gap-2 px-6 py-3 bg-[#c9512d] hover:bg-[#b74423] text-white text-xs uppercase tracking-widest rounded-xl transition duration-200 shadow-sm">
                    <span>Reservasi Sekarang</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Menu -->
        <div class="space-y-8">

            <!-- ─── HAIRCUT ─── -->
            <div class="space-y-3">
                <h3 class="pl-cat text-stone-400">Haircut</h3>

                <div class="bg-white border border-stone-200 rounded-2xl p-6 hover:border-[#c9512d]/40 transition duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex items-baseline gap-3 flex-wrap">
                                <h4 class="pl-svc-name text-lg text-stone-900">Haircut</h4>
                                <span class="pl-price text-lg text-[#c9512d]">Rp 100.000</span>
                                <span class="pl-dur text-stone-400">/ 60 Min</span>
                            </div>
                            <p class="pl-desc text-xs text-stone-500 max-w-xl">
                                Potongan rambut presisi sesuai anatomi wajah dan tekstur alami. Sudah termasuk sesi konsultasi 10 menit Define Session, cuci rambut relaksasi, dan styling.
                            </p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-1">
                                <span class="pl-addon text-[11px] text-stone-400">Opsi Tambahan:</span>
                                <span class="pl-addon-val text-[11px] text-stone-700">Long Hair (+25k)</span>
                                <span class="text-stone-300 text-xs">&bull;</span>
                                <span class="pl-addon-val text-[11px] text-stone-700">Skin Fade (+10k)</span>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <a href="{{ route('booking.index', ['service_id' => 5]) }}"
                               class="pl-btn inline-flex items-center px-5 py-2.5 bg-stone-900 hover:bg-[#c9512d] text-white text-xs uppercase tracking-wide rounded-xl transition duration-200">
                                Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── CHEMICAL PACKAGE ─── -->
            <div class="space-y-3 pt-2">
                <h3 class="pl-cat text-stone-400">Chemical Package</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div class="space-y-2">
                            <div class="flex justify-between items-baseline gap-2">
                                <h4 class="pl-svc-name text-sm text-stone-900">Smooth Flow Keratin</h4>
                                <span class="pl-price text-sm text-[#c9512d] shrink-0">499k</span>
                            </div>
                            <p class="pl-desc text-xs text-stone-500 leading-relaxed">
                                Nutrisi keratin intensif untuk rambut lurus jatuh alami, lembut, dan anti-frizz. Termasuk haircut.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-stone-100 flex items-center justify-between">
                            <span class="pl-dur text-stone-400">180 Min</span>
                            <a href="{{ route('booking.index', ['service_id' => 17]) }}" class="pl-btn text-xs text-[#c9512d] hover:text-[#b74423]">Pesan &rarr;</a>
                        </div>
                    </div>

                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div class="space-y-2">
                            <div class="flex justify-between items-baseline gap-2">
                                <h4 class="pl-svc-name text-sm text-stone-900">Design Perm</h4>
                                <span class="pl-price text-sm text-[#c9512d] shrink-0">499k</span>
                            </div>
                            <p class="pl-desc text-xs text-stone-500 leading-relaxed">
                                Menciptakan tekstur volume ikal natural modern yang mudah ditata sendiri di rumah. Termasuk haircut.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-stone-100 flex items-center justify-between">
                            <span class="pl-dur text-stone-400">180 Min</span>
                            <a href="{{ route('booking.index', ['service_id' => 7]) }}" class="pl-btn text-xs text-[#c9512d] hover:text-[#b74423]">Pesan &rarr;</a>
                        </div>
                    </div>

                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div class="space-y-2">
                            <div class="flex justify-between items-baseline gap-2">
                                <h4 class="pl-svc-name text-sm text-stone-900">Down Perm &amp; Root Lift</h4>
                                <span class="pl-price text-sm text-[#c9512d] shrink-0">299k</span>
                            </div>
                            <p class="pl-desc text-xs text-stone-500 leading-relaxed">
                                Merampingkan rambut samping sekaligus mengangkat volume bagian atas. Termasuk haircut.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-stone-100 flex items-center justify-between">
                            <span class="pl-dur text-stone-400">120 Min</span>
                            <a href="{{ route('booking.index', ['service_id' => 18]) }}" class="pl-btn text-xs text-[#c9512d] hover:text-[#b74423]">Pesan &rarr;</a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ─── URBAN EXPLORATION ─── -->
            <div class="space-y-3 pt-2">
                <h3 class="pl-cat text-stone-400">Urban Exploration</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex items-center justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div class="space-y-1">
                            <div class="flex items-baseline gap-3">
                                <h4 class="pl-svc-name text-sm text-stone-900">Hair Coloring</h4>
                                <span class="pl-price text-sm text-[#c9512d]">Rp 250.000</span>
                            </div>
                            <p class="pl-desc text-xs text-stone-500">Eksplorasi warna artistik selaras dengan skin tone Anda.</p>
                        </div>
                        <a href="{{ route('booking.index', ['service_id' => 19]) }}"
                           class="pl-btn ml-4 shrink-0 px-4 py-2 bg-stone-100 hover:bg-[#c9512d] hover:text-white text-stone-700 text-xs uppercase tracking-wide rounded-lg transition duration-200">
                            Pesan
                        </a>
                    </div>

                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex items-center justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div class="space-y-1">
                            <div class="flex items-baseline gap-3">
                                <h4 class="pl-svc-name text-sm text-stone-900">Cornrows &amp; Braids</h4>
                                <span class="pl-price text-sm text-[#c9512d]">Rp 350.000</span>
                            </div>
                            <p class="pl-desc text-xs text-stone-500">Seni kepang rambut kontemporer yang ekspresif.</p>
                        </div>
                        <a href="{{ route('booking.index', ['service_id' => 20]) }}"
                           class="pl-btn ml-4 shrink-0 px-4 py-2 bg-stone-100 hover:bg-[#c9512d] hover:text-white text-stone-700 text-xs uppercase tracking-wide rounded-lg transition duration-200">
                            Pesan
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>
