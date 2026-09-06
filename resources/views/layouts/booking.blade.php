<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Book Experience | MORE Hair Studio</title>

    <!-- Styles & Scripts -->
    @vite(['resources/css/booking.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#fafaf9] text-stone-900 min-h-screen flex flex-col antialiased pb-20 md:pb-0 font-sans">
    <!-- Navbar (Synchronized with Public Layout) -->
    <nav class="bg-white border-b border-stone-200 sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <img src="/logo/logo.png" alt="MORE Hair Studio" class="h-10 object-contain">
                    </a>
                </div>

                <!-- Navigation Links (Exact Match with Public Layout) -->
                <div class="hidden md:flex space-x-8 text-xs font-semibold uppercase tracking-wider text-stone-600">
                    <a href="{{ route('home') }}" class="hover:text-[#c9512d] transition duration-200">Beranda</a>
                    <a href="{{ route('about') }}" class="hover:text-[#c9512d] transition duration-200">Tentang Kami</a>
                    <a href="{{ route('services.index') }}" class="hover:text-[#c9512d] transition duration-200">Layanan &amp; Tarif</a>
                    <a href="{{ route('stylists.index') }}" class="hover:text-[#c9512d] transition duration-200">Hair Artists</a>
                    <a href="{{ route('outlets.index') }}" class="hover:text-[#c9512d] transition duration-200">Studio</a>
                </div>

                <!-- Back Button & Language Switcher -->
                <div class="flex items-center space-x-4">
                    <!-- Back Button -->
                    <div>
                        <a href="javascript:void(0)" onclick="if(window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('home') }}'; }" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold uppercase tracking-wider text-stone-700 bg-stone-100 hover:bg-[#c9512d] hover:text-white transition duration-200 shadow-2xs group">
                            <svg class="w-3.5 h-3.5 text-stone-500 group-hover:text-white group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali</span>
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
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[9px] uppercase font-bold text-stone-500 hover:text-stone-800 transition">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('services.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[9px] uppercase font-bold text-stone-500 hover:text-stone-800 transition">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0M12 12L9.121 9.121m0 0A3 3 0 104.879 4.879a3 3 0 004.242 4.242z"/></svg>
            <span>Layanan</span>
        </a>
        
        <!-- Active Booking Button in Center -->
        <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#c9512d] text-white text-[11px] font-semibold uppercase tracking-wider shadow-sm transition mx-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Reservasi</span>
        </a>

        <a href="{{ route('outlets.index') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[9px] uppercase font-bold text-stone-500 hover:text-stone-800 transition">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Studio</span>
        </a>
        <a href="{{ route('about') }}" class="flex flex-col items-center justify-center flex-1 py-1 text-[9px] uppercase font-bold text-stone-500 hover:text-stone-800 transition">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Tentang</span>
        </a>
    </div>

    <!-- Main Wizard Wrapper -->
    <main class="flex-grow flex items-start justify-center py-12 px-4">
        <div class="w-full max-w-7xl">
            @yield('content')
        </div>
    </main>

    <!-- Footer: Premium Dark Grooming & Lifestyle with Creative Ecosystem -->
    <footer class="bg-[#121110] text-stone-400 border-t border-stone-800 pt-16 pb-12 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Columns (Perfect Alignment & Harmony) -->
            <div class="flex flex-col md:flex-row justify-between items-start gap-12 pb-14">
                <!-- Brand Profile -->
                <div class="space-y-4 max-w-sm">
                    <div class="h-8 flex items-center">
                        <img src="/logo/logo-desc-white.png" alt="MORE Hair Studio" class="h-8 w-auto object-contain">
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
                            <h3 class="text-xs uppercase tracking-widest text-[#2563eb] font-bold font-mono">DISCOVER</h3>
                        </div>
                        <ul class="space-y-3 text-xs text-stone-400 font-normal">
                            <li><a href="{{ route('services.index') }}" class="hover:text-white transition duration-200">Our Services</a></li>
                            <li><a href="{{ route('stylists.index') }}" class="hover:text-white transition duration-200">Stylist Team</a></li>
                            <li><a href="{{ route('outlets.index') }}" class="hover:text-white transition duration-200">Studio Locations</a></li>
                        </ul>
                    </div>

                    <!-- Studio -->
                    <div class="space-y-4">
                        <div class="h-8 flex items-center">
                            <h3 class="text-xs uppercase tracking-widest text-[#2563eb] font-bold font-mono">STUDIO</h3>
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
</body>
</html>

