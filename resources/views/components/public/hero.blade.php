<section class="relative pt-24 pb-20 md:pt-32 md:pb-32 border-b border-stone-900 flex items-center min-h-[80vh] overflow-hidden">

    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img src="/images/more_studio_interior.jpg" alt="MORE Hair Studio Bandung Interior" class="w-full h-full object-cover object-center">
        <div class="absolute inset-0 bg-stone-900/80"></div>
    </div>

    <!-- FORCE font inline agar 100% tampil -->
    <style>
        .hero-badge       { font-family: 'Suisse Intl', sans-serif; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; }
        .hero-label       { font-family: 'SuisseIntlMono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; }
        .hero-title       { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 300; line-height: 1; }
        .hero-title-bold  { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1; }
        .hero-title-accent{ font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; }
        .hero-body        { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
        .hero-cta-primary { font-family: 'Suisse Intl', sans-serif !important; font-weight: 600; }
        .hero-stat-label  { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 600; }
        .hero-stat-sub    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
    </style>

    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">

        <div class="max-w-4xl space-y-10">

            <!-- Badge -->
            <div class="hero-badge text-[#e86a43] flex items-center gap-2">
                <span class="inline-block w-6 h-px bg-[#e86a43]"></span>
                MORE Hair Studio &bull; Bandung
            </div>

            <!-- HEADLINE: Stack Sans Notch mixed weight -->
            <div class="space-y-0">
                <!-- Light weight line — notch font visible -->
                <div class="hero-title text-5xl sm:text-6xl md:text-7xl lg:text-8xl text-white/60">
                    Define You,
                </div>
                <!-- Bold weight + accent color -->
                <div class="hero-title-bold text-5xl sm:text-6xl md:text-7xl lg:text-8xl">
                    <span class="text-white">MORE</span><span class="text-[#e86a43]">.</span>
                </div>
            </div>

            <!-- Body text: Suisse Intl -->
            <p class="hero-body text-stone-300 text-base sm:text-lg leading-relaxed max-w-2xl">
                Studio perawatan rambut urban dan ekosistem kreatif di Bandung. Precision Haircut, Keratin, Design Perm &amp; Down Perm — dengan konsultasi 10 menit Define Session.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('booking.index') }}"
                   class="hero-cta-primary inline-flex items-center gap-2 px-8 py-4 bg-[#c9512d] hover:bg-[#b74423] text-white text-sm uppercase tracking-wider rounded-xl transition duration-200 shadow-lg">
                    <span>Mulai Reservasi</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('outlets.index') }}"
                   class="hero-cta-primary inline-flex items-center gap-2 px-8 py-4 border border-white/30 hover:border-white hover:bg-white/5 text-white text-sm uppercase tracking-wider rounded-xl transition duration-200 backdrop-blur-sm">
                    <span>Pilih Studio &amp; Lokasi</span>
                </a>
            </div>

            <!-- Stats row: Stack Sans Notch label + Suisse Intl sub -->
            <div class="pt-6 mt-2 grid grid-cols-3 gap-6 border-t border-white/15">
                <div>
                    <span class="hero-stat-label text-sm text-white block mb-1">10-Min Define Session</span>
                    <span class="hero-stat-sub text-xs text-stone-400">Konsultasi personal sebelum potong</span>
                </div>
                <div>
                    <span class="hero-stat-label text-sm text-white block mb-1">Precision &amp; Chemical</span>
                    <span class="hero-stat-sub text-xs text-stone-400">Keratin, Perm &amp; Down Perm</span>
                </div>
                <div>
                    <span class="hero-stat-label text-sm text-white block mb-1">Sistem Booking Realtime</span>
                    <span class="hero-stat-sub text-xs text-stone-400">Jadwal instan per cabang</span>
                </div>
            </div>

        </div>
    </div>
</section>
