<section class="relative pt-24 pb-20 md:pt-32 md:pb-32 border-b border-stone-900 flex items-center min-h-[80vh] overflow-hidden">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img src="/images/more_studio_interior.jpg" alt="MORE Hair Studio Bandung Interior" class="w-full h-full object-cover object-center">
        <!-- Opacity Overlay -->
        <div class="absolute inset-0 bg-stone-900/75"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        
        <div class="max-w-3xl space-y-8">
            
            <div class="text-xs uppercase tracking-wider text-[#e86a43] font-semibold tracking-[0.2em]">
                MORE Hair Studio &bull; Bandung
            </div>

            <div class="space-y-6">
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-[4rem] font-bold text-white tracking-tight leading-[1.1]">
                    Define You, <span class="text-[#e86a43]">MORE.</span>
                </h1>
                <p class="text-stone-300 text-base sm:text-lg leading-relaxed max-w-2xl font-light">
                    Studio perawatan rambut urban dan ekosistem kreatif di Bandung. Kami menghadirkan seni potong presisi, perawatan tekstur rambut terkini (Keratin, Design Perm, Down Perm), serta sesi konsultasi 10 menit Define Session untuk menemukan gaya yang autentik bagi karakter Anda.
                </p>
            </div>

            <!-- Clean Call to Actions -->
            <div class="pt-4 flex flex-wrap gap-4">
                <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-[#c9512d] hover:bg-[#b74423] text-white text-sm font-semibold uppercase tracking-wider rounded-xl transition duration-200 shadow-lg">
                    <span>Mulai Reservasi</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('outlets.index') }}" class="inline-flex items-center gap-2 px-8 py-4 border border-white/30 hover:border-white hover:bg-white/5 text-white text-sm font-semibold uppercase tracking-wider rounded-xl transition duration-200 backdrop-blur-sm">
                    <span>Pilih Studio &amp; Lokasi</span>
                </a>
            </div>

            <!-- 3 Clean Key Standards -->
            <div class="pt-8 mt-8 grid grid-cols-1 sm:grid-cols-3 gap-6 border-t border-white/20">
                <div>
                    <span class="text-sm font-bold text-white block mb-1">10-Min Define Session</span>
                    <span class="text-xs text-stone-400">Konsultasi personal sebelum potong</span>
                </div>
                <div>
                    <span class="text-sm font-bold text-white block mb-1">Precision &amp; Chemical</span>
                    <span class="text-xs text-stone-400">Keratin, Perm &amp; Down Perm</span>
                </div>
                <div>
                    <span class="text-sm font-bold text-white block mb-1">Sistem Booking Realtime</span>
                    <span class="text-xs text-stone-400">Jadwal instan per cabang</span>
                </div>
            </div>

        </div>

    </div>
</section>
