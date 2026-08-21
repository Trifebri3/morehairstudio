<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MORE Hair Studio | Premium Grooming & Lifestyle Experience')</title>

    <!-- Meta SEO -->
    <meta name="description" content="@yield('meta_description', 'Book premium haircuts, coloring, and treatments at MORE Hair Studio. Experience custom luxury rituals.')">

    <!-- Styles & Scripts -->
    @vite(['resources/css/public.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#fafaf9] text-stone-900 min-h-screen flex flex-col antialiased pb-20 md:pb-0 font-sans">
    <!-- Navbar -->
    <nav class="bg-white border-b border-stone-150 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <img src="/logo/logo.png" alt="MORE Hair Studio" class="h-10 object-contain">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-10 text-xs font-bold uppercase tracking-widest text-stone-700">
                    <a href="{{ route('home') }}" class="hover:text-[#0A3D91] transition duration-300">Home</a>
                    <a href="{{ route('booking.index') }}" class="hover:text-[#0A3D91] transition duration-300">Book Experience</a>
                    <a href="{{ route('outlets.index') }}" class="hover:text-[#0A3D91] transition duration-300">Location Lounge</a>
                    <a href="{{ route('services.index') }}" class="hover:text-[#0A3D91] transition duration-300">Services</a>
                </div>

                <!-- CTA Button & Language Switcher -->
                <div class="flex items-center space-x-6">
                    <div class="hidden md:block">
                        <a href="{{ route('booking.index') }}" class="inline-flex items-center px-6 py-2.5 rounded-lg text-xs font-bold uppercase tracking-widest bg-[#0A3D91] text-white hover:bg-[#062e70] transition duration-300 shadow-sm">
                            Book Experience
                        </a>
                    </div>
                    
                    <!-- Language switcher -->
                    <div class="flex items-center space-x-2 border-l border-stone-200 pl-4 text-xs font-bold font-mono">
                        <a href="{{ route('locale.switch', 'id') }}" class="{{ session('locale', 'id') === 'id' ? 'text-[#0A3D91]' : 'text-stone-400' }} hover:text-[#0A3D91]">ID</a>
                        <span class="text-stone-300">|</span>
                        <a href="{{ route('locale.switch', 'en') }}" class="{{ session('locale', 'id') === 'en' ? 'text-[#0A3D91]' : 'text-stone-400' }} hover:text-[#0A3D91]">EN</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Bottom Navigation Bar (Minimalist Typography, No Emojis) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-stone-200 py-5 flex justify-around items-center z-50 shadow px-4 rounded-t-xl">
        <a href="{{ route('home') }}" class="text-[10px] tracking-widest uppercase font-extrabold {{ Route::currentRouteName() === 'home' ? 'text-[#0A3D91]' : 'text-stone-500' }} transition">
            Home
        </a>
        <a href="{{ route('booking.index') }}" class="text-[10px] tracking-widest uppercase font-extrabold {{ Route::currentRouteName() === 'booking.index' ? 'text-[#0A3D91]' : 'text-stone-500' }} transition">
            Book
        </a>
        <a href="{{ route('services.index') }}" class="text-[10px] tracking-widest uppercase font-extrabold {{ Route::currentRouteName() === 'services.index' ? 'text-[#0A3D91]' : 'text-stone-500' }} transition">
            Services
        </a>
        <a href="{{ route('outlets.index') }}" class="text-[10px] tracking-widest uppercase font-extrabold {{ Route::currentRouteName() === 'outlets.index' ? 'text-[#0A3D91]' : 'text-stone-500' }} transition">
            Locations
        </a>
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#1c1917] text-stone-400 border-t border-stone-800 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-2 space-y-4">
                    <img src="/logo/logo.png" alt="MORE Hair Studio" class="h-8 object-contain brightness-0 invert opacity-95">
                    <p class="text-xs text-stone-400 max-w-sm leading-relaxed">
                        A modern grooming experience built around your style, your story, and your moment.
                    </p>
                </div>
                <!-- Links -->
                <div>
                    <h3 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold mb-4">Discover</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('services.index') }}" class="hover:text-white transition">Our Services</a></li>
                        <li><a href="{{ route('stylists.index') }}" class="hover:text-white transition">Stylist Team</a></li>
                        <li><a href="{{ route('outlets.index') }}" class="hover:text-white transition">Studio Locations</a></li>
                    </ul>
                </div>
                <!-- Legals -->
                <div>
                    <h3 class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold mb-4">Studio</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition">Terms & Conditions</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-16 pt-8 border-t border-stone-800 flex flex-col md:flex-row justify-between text-[10px] text-stone-500 uppercase tracking-wider font-bold">
                <p>&copy; {{ date('Y') }} MORE Hair Studio. All rights reserved.</p>
                <p class="mt-2 md:mt-0">Premium Grooming &amp; Lifestyle</p>
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
        class="fixed bottom-6 left-6 right-6 md:left-auto md:max-w-md bg-stone-900/95 backdrop-blur-md text-white p-6 rounded-3xl shadow-2xl border border-stone-850 z-50 space-y-4"
        style="display: none;"
    >
        <div class="space-y-1.5">
            <h4 class="text-xs font-black uppercase tracking-wider text-amber-400">Pemberitahuan Cookie & Privasi</h4>
            <p class="text-[10px] text-stone-300 leading-relaxed font-light">
                Kami menggunakan cookie untuk menganalisis lalu lintas web (IP, lokasi, perangkat, pencarian) serta demografi guna meningkatkan strategi layanan kami sesuai dengan <a href="{{ route('privacy') }}" class="underline text-amber-300 hover:text-amber-400">Kebijakan Privasi</a>.
            </p>
        </div>
        <div class="flex items-center justify-end space-x-3 text-[10px] font-black uppercase tracking-widest pt-2">
            <button @click="declineAll" class="text-stone-400 hover:text-white transition px-2 py-1">Tolak</button>
            <button @click="acceptAll" class="bg-amber-500 text-stone-950 px-4 py-2 rounded-xl hover:bg-amber-400 transition shadow-md shadow-amber-500/10">Terima Semua</button>
        </div>
    </div>

</body>
</html>
