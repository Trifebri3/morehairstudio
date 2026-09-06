<section class="bg-white pt-8 pb-16 md:pt-16 md:pb-24 border-b border-stone-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            
            <!-- Left: Brand Inlook & Value (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <div class="text-xs uppercase tracking-wider text-[#c9512d] font-semibold">
                    MORE Hair Studio &bull; Bandung
                </div>

                <div class="space-y-4">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-bold text-stone-900 tracking-tight leading-[1.12]">
                        Define You, <span class="text-[#c9512d]">MORE.</span>
                    </h1>
                    <p class="text-stone-600 text-sm sm:text-base leading-relaxed max-w-xl font-normal">
                        Studio perawatan rambut urban dan ekosistem kreatif di Bandung. Kami menghadirkan seni potong presisi, perawatan tekstur rambut terkini (Keratin, Design Perm, Down Perm), serta sesi konsultasi 10 menit Define Session untuk menemukan gaya yang autentik bagi karakter Anda.
                    </p>
                </div>

                <!-- Clean Call to Actions -->
                <div class="pt-2 flex flex-wrap gap-4">
                    <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-[#c9512d] hover:bg-[#b74423] text-white text-xs font-semibold uppercase tracking-wider rounded-xl transition duration-200 shadow-2xs">
                        <span>Mulai Reservasi</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('outlets.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 border border-stone-300 hover:border-[#c9512d] hover:text-[#c9512d] text-stone-800 text-xs font-semibold uppercase tracking-wider rounded-xl transition duration-200 bg-white">
                        <span>Pilih Studio &amp; Lokasi</span>
                    </a>
                </div>

                <!-- 3 Clean Key Standards -->
                <div class="pt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-stone-100">
                    <div>
                        <span class="text-xs font-bold text-stone-900 block">10-Min Define Session</span>
                        <span class="text-[11px] text-stone-500">Konsultasi personal sebelum potong</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-stone-900 block">Precision &amp; Chemical</span>
                        <span class="text-[11px] text-stone-500">Keratin, Perm &amp; Down Perm</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-stone-900 block">Sistem Booking Realtime</span>
                        <span class="text-[11px] text-stone-500">Jadwal instan per cabang</span>
                    </div>
                </div>

            </div>

            <!-- Right: Authentic Studio Interior Photo (5 cols) -->
            <div class="lg:col-span-5">
                <div class="rounded-2xl overflow-hidden border border-stone-200 bg-stone-50 shadow-xs">
                    <img src="/images/more_studio_interior.jpg" alt="MORE Hair Studio Bandung Interior" class="w-full aspect-4/3 sm:aspect-square object-cover">
                    <div class="p-4 bg-white border-t border-stone-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-stone-900 block">MORE Hair Studio</span>
                            <span class="text-stone-500 text-[11px]">Suaka perawatan rambut &amp; ruang kreatif</span>
                        </div>
                        <a href="{{ route('about') }}" class="text-[#c9512d] hover:underline font-semibold text-xs">
                            Profil Kami &rarr;
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>
