@props([
    'services' => null
])

<section id="pricelist" class="py-16 md:py-20 bg-white border-b border-stone-100 font-sans">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <!-- Minimal Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-stone-200">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-[#c9512d] font-semibold block mb-1">
                    Price List &bull; Daftar Layanan
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">
                    Layanan &amp; Tarif
                </h2>
                <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-lg">
                    Harga transparan tanpa biaya tersembunyi. Layanan potong rambut sudah termasuk 10-menit Define Session, hair wash, dan styling.
                </p>
            </div>
            <div>
                <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-[#c9512d] hover:bg-[#b74423] text-white text-xs font-semibold rounded-xl transition duration-200 shadow-2xs">
                    <span>Reservasi Sekarang</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Clean Editorial Menu List -->
        <div class="space-y-6">
            
            <!-- Category 1: Haircut & Ritual -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400">Haircut</h3>
                
                <div class="bg-white border border-stone-200 rounded-2xl p-5 hover:border-[#c9512d]/40 transition duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-baseline gap-3">
                                <h4 class="text-base font-bold text-stone-900">Haircut</h4>
                                <span class="text-sm font-bold text-[#c9512d]">Rp 100.000</span>
                                <span class="text-xs text-stone-400 font-normal">/ 60 Menit</span>
                            </div>
                            <p class="text-xs text-stone-600 leading-relaxed">
                                Potongan rambut presisi sesuai anatomi wajah dan tekstur alami. Sudah termasuk sesi konsultasi 10 menit Define Session, cuci rambut relaksasi, dan styling.
                            </p>
                            <div class="pt-1 text-[11px] text-stone-500 flex flex-wrap gap-4">
                                <span>Opsi Tambahan:</span>
                                <span class="text-stone-700 font-medium">Long Hair (+25k)</span>
                                <span class="text-stone-300">&bull;</span>
                                <span class="text-stone-700 font-medium">Skin Fade (+10k)</span>
                            </div>
                        </div>
                        <div class="shrink-0 pt-2 sm:pt-0">
                            <a href="{{ route('booking.index', ['service_id' => 5]) }}" class="inline-flex items-center px-4 py-2 bg-stone-900 hover:bg-[#c9512d] text-white text-xs font-medium rounded-xl transition duration-200">
                                Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 2: Chemical Packages -->
            <div class="space-y-3 pt-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400">Chemical Package</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- Smooth Flow Keratin -->
                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div class="space-y-2">
                            <div class="flex justify-between items-baseline">
                                <h4 class="text-sm font-bold text-stone-900">Smooth Flow Keratin</h4>
                                <span class="text-sm font-bold text-[#c9512d]">499k</span>
                            </div>
                            <p class="text-xs text-stone-500 leading-relaxed">
                                Nutrisi keratin intensif untuk rambut lurus jatuh alami, lembut, dan anti-frizz. Termasuk haircut.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-stone-100 flex items-center justify-between">
                            <span class="text-[11px] text-stone-400">180 Min</span>
                            <a href="{{ route('booking.index', ['service_id' => 17]) }}" class="text-xs font-semibold text-[#c9512d] hover:text-[#b74423]">
                                Pesan &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Design Perm -->
                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div class="space-y-2">
                            <div class="flex justify-between items-baseline">
                                <h4 class="text-sm font-bold text-stone-900">Design Perm</h4>
                                <span class="text-sm font-bold text-[#c9512d]">499k</span>
                            </div>
                            <p class="text-xs text-stone-500 leading-relaxed">
                                Menciptakan tekstur volume ikal natural modern yang mudah ditata sendiri di rumah. Termasuk haircut.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-stone-100 flex items-center justify-between">
                            <span class="text-[11px] text-stone-400">180 Min</span>
                            <a href="{{ route('booking.index', ['service_id' => 7]) }}" class="text-xs font-semibold text-[#c9512d] hover:text-[#b74423]">
                                Pesan &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Down Perm & Root Lift -->
                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div class="space-y-2">
                            <div class="flex justify-between items-baseline">
                                <h4 class="text-sm font-bold text-stone-900">Down Perm &amp; Root Lift</h4>
                                <span class="text-sm font-bold text-[#c9512d]">299k</span>
                            </div>
                            <p class="text-xs text-stone-500 leading-relaxed">
                                Merampingkan rambut samping yang jigrak sekaligus mengangkat volume rambut bagian atas. Termasuk haircut.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-stone-100 flex items-center justify-between">
                            <span class="text-[11px] text-stone-400">120 Min</span>
                            <a href="{{ route('booking.index', ['service_id' => 18]) }}" class="text-xs font-semibold text-[#c9512d] hover:text-[#b74423]">
                                Pesan &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Category 3: Urban Exploration -->
            <div class="space-y-3 pt-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400">Urban Exploration</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex items-center justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div>
                            <div class="flex items-baseline gap-3">
                                <h4 class="text-sm font-bold text-stone-900">Hair Coloring</h4>
                                <span class="text-sm font-bold text-[#c9512d]">Rp 250.000</span>
                            </div>
                            <p class="text-xs text-stone-500 mt-1">Eksplorasi warna artistik selaras dengan skin tone Anda.</p>
                        </div>
                        <a href="{{ route('booking.index', ['service_id' => 19]) }}" class="px-3.5 py-1.5 bg-stone-100 hover:bg-[#c9512d] hover:text-white text-stone-800 text-xs font-medium rounded-lg transition duration-200 shrink-0 ml-4">
                            Pesan
                        </a>
                    </div>

                    <div class="bg-white border border-stone-200 rounded-2xl p-5 flex items-center justify-between hover:border-[#c9512d]/40 transition duration-200">
                        <div>
                            <div class="flex items-baseline gap-3">
                                <h4 class="text-sm font-bold text-stone-900">Cornrows &amp; Braids</h4>
                                <span class="text-sm font-bold text-[#c9512d]">Rp 350.000</span>
                            </div>
                            <p class="text-xs text-stone-500 mt-1">Seni kepang rambut kontemporer.</p>
                        </div>
                        <a href="{{ route('booking.index', ['service_id' => 20]) }}" class="px-3.5 py-1.5 bg-stone-100 hover:bg-[#c9512d] hover:text-white text-stone-800 text-xs font-medium rounded-lg transition duration-200 shrink-0 ml-4">
                            Pesan
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>
