<style>
/* ── BRAND PROFILE FONTS ── */
.bp-badge    { font-family: 'Suisse Intl', sans-serif; font-size: 0.7rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600; }
.bp-h2       { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; }
.bp-body     { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; line-height: 1.7; }
.bp-caption  { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; }
.bp-sub      { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
.bp-num      { font-family: 'SuisseIntlMono', monospace !important; font-weight: 700; }
.bp-link     { font-family: 'Suisse Intl', sans-serif !important; font-weight: 600; }
</style>

<section class="py-16 md:py-24 bg-[#fafaf9] border-b border-stone-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">

            <!-- Left: Photo -->
            <div class="lg:col-span-5 order-2 lg:order-1">
                <div class="rounded-2xl overflow-hidden border border-stone-200 bg-white shadow-sm">
                    <img src="/images/gallery-7.jpg" alt="Client Experience di MORE Hair Studio" class="w-full aspect-4/3 sm:aspect-square object-cover">
                    <div class="p-4 bg-white border-t border-stone-100">
                        <span class="bp-caption text-stone-900 block">Candid &amp; Effortless</span>
                        <span class="bp-sub text-[11px] text-stone-500 block mt-0.5">Hasil potongan presisi yang mudah dirawat dan ditata sendiri</span>
                    </div>
                </div>
            </div>

            <!-- Right: Story -->
            <div class="lg:col-span-7 space-y-6 order-1 lg:order-2">

                <span class="bp-badge text-[#c9512d]">Tentang MORE Hair Studio</span>

                <div class="space-y-4">
                    <h2 class="bp-h2 text-2xl sm:text-3xl md:text-4xl text-stone-900">
                        Mendefinisikan Karakter Melalui Rambut
                    </h2>
                    <p class="bp-body text-stone-600 text-sm sm:text-base">
                        Bagi kami, rambut adalah medium paling jujur untuk mendefinisikan seorang manusia. MORE Hair Studio lahir dari dorongan untuk mengembalikan esensi grooming kepada pemiliknya — menghargai tekstur alami, bentuk wajah, dan gaya hidup Anda tanpa memaksakan cetakan seragam yang kaku.
                    </p>
                </div>

                <!-- 3 Highlights -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-4">
                        <div class="bp-num w-7 h-7 rounded-full bg-[#faede7] text-[#c9512d] text-xs flex items-center justify-center shrink-0 mt-0.5">1</div>
                        <div>
                            <h4 class="bp-caption text-stone-900">10-Min Define Session</h4>
                            <p class="bp-sub text-xs text-stone-500 mt-1 leading-relaxed">Sesi diskusi sebelum pemotongan untuk menganalisis bentuk kepala dan arah pusaran alami helai rambut.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="bp-num w-7 h-7 rounded-full bg-[#faede7] text-[#c9512d] text-xs flex items-center justify-center shrink-0 mt-0.5">2</div>
                        <div>
                            <h4 class="bp-caption text-stone-900">Spektrum Layanan Presisi</h4>
                            <p class="bp-sub text-xs text-stone-500 mt-1 leading-relaxed">Precision Cut, Smooth Flow Keratin, Design Perm, Down Perm, hingga Hair Coloring &amp; Braids.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="bp-num w-7 h-7 rounded-full bg-[#faede7] text-[#c9512d] text-xs flex items-center justify-center shrink-0 mt-0.5">3</div>
                        <div>
                            <h4 class="bp-caption text-stone-900">Ekosistem Kreatif Urban</h4>
                            <p class="bp-sub text-xs text-stone-500 mt-1 leading-relaxed">Ruang berkarya inklusif di Bandung, mempertemukan barber berbakat dengan komunitas kreatif.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ route('about') }}" class="bp-link inline-flex items-center gap-1.5 text-xs text-[#c9512d] hover:text-[#b74423]">
                        <span>Baca Profil Lengkap Kami</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
