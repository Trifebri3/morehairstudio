<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Comprehensive SEO, Open Graph, Twitter Card & Local Schema -->
    @include('partials.seo')

    <!-- Styles & Scripts -->
    @vite(['resources/css/public.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- ═══ FONT PRELOAD — Static Weights ═══ -->
    <link rel="preload" href="/fonts/StackSansNotch-Bold.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="/fonts/StackSansNotch-Light.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlTrial-Regular.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlTrial-Light.otf" as="font" type="font/otf" crossorigin>

    <!-- ═══ INLINE FONT-FACE (STATIC WEIGHTS) ═══ -->
    <style>
        /* Stack Sans Notch */
        @font-face {
            font-family: 'Stack Sans Notch';
            src: url('/fonts/StackSansNotch-Light.ttf') format('truetype');
            font-weight: 300; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Stack Sans Notch';
            src: url('/fonts/StackSansNotch-Regular.ttf') format('truetype');
            font-weight: 400; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Stack Sans Notch';
            src: url('/fonts/StackSansNotch-Medium.ttf') format('truetype');
            font-weight: 500; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Stack Sans Notch';
            src: url('/fonts/StackSansNotch-SemiBold.ttf') format('truetype');
            font-weight: 600; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Stack Sans Notch';
            src: url('/fonts/StackSansNotch-Bold.ttf') format('truetype');
            font-weight: 700; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Stack Sans Notch';
            src: url('/fonts/StackSansNotch-Bold.ttf') format('truetype');
            font-weight: 800; font-style: normal; font-display: swap;
        }

        /* Suisse Intl */
        @font-face {
            font-family: 'Suisse Intl';
            src: url('/fonts/SuisseIntlTrial-Light.otf') format('opentype');
            font-weight: 300; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Suisse Intl';
            src: url('/fonts/SuisseIntlTrial-Regular.otf') format('opentype');
            font-weight: 400; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Suisse Intl';
            src: url('/fonts/SuisseIntlTrial-Medium.otf') format('opentype');
            font-weight: 500; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Suisse Intl';
            src: url('/fonts/SuisseIntlTrial-Bold.otf') format('opentype');
            font-weight: 700; font-style: normal; font-display: swap;
        }

        /* Suisse Intl Mono */
        @font-face {
            font-family: 'SuisseIntlMono';
            src: url('/fonts/SuisseIntlMonoTrial-Regular.otf') format('opentype');
            font-weight: 400; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'SuisseIntlMono';
            src: url('/fonts/SuisseIntlMonoTrial-Bold.otf') format('opentype');
            font-weight: 700; font-style: normal; font-display: swap;
        }

        /* ── GLOBAL FONT RULES (bulletproof override) ── */
        html, body, p, span, div, li, td, th, label, a, input, button, select, textarea {
            font-family: 'Suisse Intl', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        }
        h1, h2, h3, h4, h5, h6,
        .font-display, .font-headline,
        [class*="text-2xl"], [class*="text-3xl"],
        [class*="text-4xl"], [class*="text-5xl"],
        [class*="text-6xl"], [class*="text-7xl"],
        [class*="text-8xl"], [class*="text-9xl"] {
            font-family: 'Stack Sans Notch', -apple-system, BlinkMacSystemFont, sans-serif !important;
            letter-spacing: -0.02em;
        }
        .font-mono, code, kbd, pre, samp {
            font-family: 'SuisseIntlMono', 'Courier New', monospace !important;
            letter-spacing: 0;
        }
    </style>

    <style>
        /* ── Loading Progress Bar ── */
        #page-loader {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 3px;
            background: linear-gradient(90deg, #c9512d, #f97316, #c9512d);
            background-size: 200% 100%;
            z-index: 9999;
            transform-origin: left;
            transform: scaleX(0);
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            animation: shimmer-bar 1.5s infinite linear;
        }
        #page-loader.loading  { transform: scaleX(0.75); }
        #page-loader.complete { transform: scaleX(1); opacity: 0; transition: transform 0.2s ease, opacity 0.3s ease 0.1s; }
        @keyframes shimmer-bar {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ── Page Fade-In ── */
        #page-content { opacity: 0; transform: translateY(8px); transition: opacity 0.4s cubic-bezier(0.16,1,0.3,1), transform 0.4s cubic-bezier(0.16,1,0.3,1); }
        #page-content.visible { opacity: 1; transform: translateY(0); }
        body.page-leaving #page-content { opacity: 0; transform: translateY(-6px); transition: opacity 0.2s ease, transform 0.2s ease; }

        /* ── Smooth all links & buttons ── */
        a, button { transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease, opacity 0.2s ease; }
        a:active, button:active { transform: scale(0.97); }

        /* ── Smooth scroll ── */
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-[#fafaf9] text-stone-900 min-h-screen flex flex-col antialiased pb-20 md:pb-0 font-sans">

    <!-- Loading Progress Bar -->
    <div id="page-loader"></div>
    <!-- Navbar -->
    <nav class="bg-white border-b border-stone-200 sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <img src="/logo/logo.png?v=3" alt="MORE Hair Studio" class="h-10 object-contain">
                    </a>
                </div>

                <!-- Navigation Links (Clean, modern sans) -->
                <div class="hidden md:flex space-x-8 text-xs font-semibold uppercase tracking-wider text-stone-600">
                    <a href="{{ route('home') }}" class="{{ Route::currentRouteName() === 'home' ? 'text-[#c9512d] font-bold' : 'hover:text-[#c9512d]' }} transition duration-200">Beranda</a>
                    <a href="{{ route('about') }}" class="{{ Route::currentRouteName() === 'about' ? 'text-[#c9512d] font-bold' : 'hover:text-[#c9512d]' }} transition duration-200">Tentang Kami</a>
                    <a href="{{ route('services.index') }}" class="{{ Route::currentRouteName() === 'services.index' ? 'text-[#c9512d] font-bold' : 'hover:text-[#c9512d]' }} transition duration-200">Layanan &amp; Tarif</a>
                    <a href="{{ route('stylists.index') }}" class="{{ Route::currentRouteName() === 'stylists.index' ? 'text-[#c9512d] font-bold' : 'hover:text-[#c9512d]' }} transition duration-200">Hair Artists</a>
                    <a href="{{ route('schedule.index') }}" class="{{ Route::currentRouteName() === 'schedule.index' ? 'text-[#c9512d] font-bold' : 'hover:text-[#c9512d]' }} transition duration-200">Cek Jadwal</a>
                    <a href="{{ route('outlets.index') }}" class="{{ str_starts_with(Route::currentRouteName(), 'outlets') ? 'text-[#c9512d] font-bold' : 'hover:text-[#c9512d]' }} transition duration-200">Studio</a>
                </div>

                <!-- CTA Button & Language Switcher -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:block">
                        <a href="{{ route('booking.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl text-xs font-semibold uppercase tracking-wider bg-[#c9512d] text-white hover:bg-[#b74423] transition duration-200 shadow-2xs">
                            Reservasi
                        </a>
                    </div>
                    
                    <!-- Language switcher -->
                    <div class="flex items-center space-x-2 border-l border-stone-200 pl-4 text-xs font-bold font-mono">
                        <a href="{{ route('locale.switch', 'id') }}" class="{{ session('locale', 'id') === 'id' ? 'text-[#c9512d]' : 'text-stone-400' }} hover:text-[#c9512d] transition">ID</a>
                        <span class="text-stone-300">|</span>
                        <a href="{{ route('locale.switch', 'en') }}" class="{{ session('locale', 'id') === 'en' ? 'text-[#c9512d]' : 'text-stone-400' }} hover:text-[#c9512d] transition">EN</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Bottom Navigation Bar with Modern Icons -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-stone-200 py-2 px-3 flex items-center justify-between z-50 shadow-lg">
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[9px] uppercase font-bold {{ Route::currentRouteName() === 'home' ? 'text-[#c9512d]' : 'text-stone-500 hover:text-stone-800' }} transition">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('services.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[9px] uppercase font-bold {{ Route::currentRouteName() === 'services.index' ? 'text-[#c9512d]' : 'text-stone-500 hover:text-stone-800' }} transition">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0M12 12L9.121 9.121m0 0A3 3 0 104.879 4.879a3 3 0 004.242 4.242z"/></svg>
            <span>Layanan</span>
        </a>
        
        <!-- Prominent Booking Button in Center -->
        <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#c9512d] hover:bg-[#b74423] text-white text-[11px] font-semibold uppercase tracking-wider shadow-sm transition mx-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Reservasi</span>
        </a>

        <a href="{{ route('outlets.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[9px] uppercase font-bold {{ str_starts_with(Route::currentRouteName() ?? '', 'outlets') ? 'text-[#c9512d]' : 'text-stone-500 hover:text-stone-800' }} transition">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Studio</span>
        </a>
        <a href="{{ route('about') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[9px] uppercase font-bold {{ Route::currentRouteName() === 'about' ? 'text-[#c9512d]' : 'text-stone-500 hover:text-stone-800' }} transition">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Tentang</span>
        </a>
    </div>

    <!-- Main Content -->
    <div id="page-content">
        <main class="flex-grow">
            @yield('content')
        </main>
    </div>

    <!-- Local Fonts Preload: Stack Sans Notch (Headline) + Suisse Intl (Body) -->
    <link rel="preload" href="/fonts/StackSansNotch-Regular.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="/fonts/StackSansNotch-Bold.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlTrial-Regular.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlTrial-Medium.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlTrial-Bold.otf" as="font" type="font/otf" crossorigin>

    <!-- Footer: Premium Dark Grooming & Lifestyle with Creative Ecosystem -->
    <footer class="bg-[#121110] text-stone-400 border-t border-stone-800 pt-16 pb-12 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Columns (Perfect Alignment & Harmony) -->
            <div class="flex flex-col md:flex-row justify-between items-start gap-12 pb-14">
                <!-- Brand Profile -->
                <div class="space-y-4 max-w-sm">
                    <div class="h-8 flex items-center">
                        <img src="/logo/logo-desc-white.png?v=3" alt="MORE Hair Studio" class="h-8 w-auto object-contain">
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed font-normal">
                        A modern grooming experience built around your style, your story, and your moment.
                    </p>
                </div>

                <!-- Navigation Columns (Discover & Studio) -->
                <div class="flex space-x-16 sm:space-x-24">
                    <!-- Discover -->
                    <div class="space-y-4">
                        <div class="h-8 flex items-center">
                            <h3 class="text-xs uppercase tracking-widest text-[#2563eb] font-bold font-mono font-display">DISCOVER</h3>
                        </div>
                        <ul class="space-y-3 text-xs text-stone-400 font-normal">
                            <li><a href="{{ route('services.index') }}" class="hover:text-white transition duration-200">Our Services</a></li>
                            <li><a href="{{ route('stylists.index') }}" class="hover:text-white transition duration-200">Stylist Team</a></li>
                            <li><a href="{{ route('schedule.index') }}" class="hover:text-white transition duration-200">Jadwal &amp; Ketersediaan</a></li>
                            <li><a href="{{ route('outlets.index') }}" class="hover:text-white transition duration-200">Studio Locations</a></li>
                        </ul>
                    </div>

                    <!-- Studio -->
                    <div class="space-y-4">
                        <div class="h-8 flex items-center">
                            <h3 class="text-xs uppercase tracking-widest text-[#2563eb] font-bold font-mono font-display">STUDIO</h3>
                        </div>
                        <ul class="space-y-3 text-xs text-stone-400 font-normal">
                            <li><a href="{{ route('terms') }}" class="hover:text-white transition duration-200">Terms &amp; Conditions</a></li>
                            <li><a href="{{ route('privacy') }}" class="hover:text-white transition duration-200">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Ecosystem & Initiatives (konten.png) -->
            <div class="py-12 border-t border-stone-800/80">
                <div class="flex flex-col items-center text-center space-y-6">
                    <span class="text-[10px] uppercase tracking-[0.25em] text-stone-500 font-semibold font-mono">Creative Ecosystem &amp; Cultural Initiatives</span>
                    <div class="w-full flex justify-center">
                        <img src="/logo/konten.png" alt="MORE Hair Studio Creative Ecosystem - DEFINE SESSION, spotlight, HUMAN HAIR EDU, MORE .FM, Sound & Space, Off The-Chair, in the space" class="max-w-xl md:max-w-2xl lg:max-w-3xl w-full h-auto object-contain opacity-90 hover:opacity-100 transition duration-300">
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="pt-8 border-t border-stone-800/80 flex flex-col sm:flex-row justify-between items-center text-[10px] sm:text-xs text-stone-500 uppercase tracking-wider font-semibold gap-4">
                <p>&copy; {{ date('Y') }} MORE HAIR STUDIO. ALL RIGHTS RESERVED.</p>
                <p>PREMIUM GROOMING &amp; LIFESTYLE</p>
            </div>
        </div>
    </footer>

    <!-- Cookie & Privacy Consent Banner -->
    <div 
        x-data="{ 
            showConsent: !localStorage.getItem('morehair_cookie_consent'),
            acceptAll() {
                document.cookie = 'morehair_cookie_consent=accepted; path=/; max-age=' + (60*60*24*365);
                localStorage.setItem('morehair_cookie_consent', 'accepted');
                this.showConsent = false;
                window.location.reload();
            },
            declineAll() {
                document.cookie = 'morehair_cookie_consent=declined; path=/; max-age=' + (60*60*24*365);
                localStorage.setItem('morehair_cookie_consent', 'declined');
                this.showConsent = false;
                window.location.reload();
            }
        }"
        x-show="showConsent"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-10"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-10"
        class="fixed bottom-6 left-6 right-6 md:left-auto md:max-w-md bg-white border border-stone-200 text-stone-900 p-6 rounded-2xl shadow-2xl z-50 space-y-4"
        style="display: none;"
    >
        <div class="space-y-1.5">
            <h4 class="text-xs font-bold uppercase tracking-wider text-[#c9512d] font-mono font-display">Pemberitahuan Cookie &amp; Privasi</h4>
            <p class="text-xs text-stone-600 leading-relaxed font-light">
                Kami menggunakan cookie untuk menganalisis lalu lintas web serta demografi guna meningkatkan strategi layanan kami sesuai dengan <a href="{{ route('privacy') }}" class="underline text-[#c9512d] hover:text-[#b74423]">Kebijakan Privasi</a>.
            </p>
        </div>
        <div class="flex items-center justify-end space-x-3 text-xs font-bold uppercase tracking-wider pt-2 font-mono">
            <button @click="declineAll" class="text-stone-500 hover:text-stone-900 transition px-3 py-1.5">Tolak</button>
            <button @click="acceptAll" class="bg-[#c9512d] text-white px-4 py-2 rounded-xl hover:bg-[#b74423] transition shadow-sm">Terima Semua</button>
        </div>
    </div>

    <!-- Page Transition Script -->
    <script>
    (function() {
        const loader  = document.getElementById('page-loader');
        const content = document.getElementById('page-content');
        function startLoader() { if (loader) loader.classList.add('loading'); }
        function finishLoader() {
            if (!loader) return;
            loader.classList.remove('loading');
            loader.classList.add('complete');
            setTimeout(() => { loader.classList.remove('complete'); loader.style.opacity = ''; }, 500);
        }
        function revealContent() {
            if (content) requestAnimationFrame(() => requestAnimationFrame(() => content.classList.add('visible')));
        }
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript') ||
                href.startsWith('http') || href.startsWith('mailto') ||
                link.target === '_blank' || e.ctrlKey || e.metaKey) return;
            e.preventDefault();
            startLoader();
            document.body.classList.add('page-leaving');
            setTimeout(() => { window.location.href = href; }, 200);
        });
        startLoader();
        if (document.readyState === 'complete') { finishLoader(); revealContent(); }
        else {
            window.addEventListener('load', () => { finishLoader(); revealContent(); });
            document.addEventListener('DOMContentLoaded', revealContent);
        }
    })();
    </script>

</body>
</html>

