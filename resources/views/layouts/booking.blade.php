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
    <!-- Header -->
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
                    <a href="{{ route('booking.index') }}" class="text-[#0A3D91] font-extrabold">Book Experience</a>
                    <a href="{{ route('outlets.index') }}" class="hover:text-[#0A3D91] transition duration-300">Location Lounge</a>
                    <a href="{{ route('services.index') }}" class="hover:text-[#0A3D91] transition duration-300">Services</a>
                </div>

                <!-- Right items (Exit & Language Switcher) -->
                <div class="flex items-center space-x-6">
                    <!-- Language switcher -->
                    <div class="flex items-center space-x-2 text-xs font-bold font-mono">
                        <a href="{{ route('locale.switch', 'id') }}" class="{{ session('locale', 'id') === 'id' ? 'text-[#0A3D91]' : 'text-stone-400' }} hover:text-[#0A3D91]">ID</a>
                        <span class="text-stone-300">|</span>
                        <a href="{{ route('locale.switch', 'en') }}" class="{{ session('locale', 'id') === 'en' ? 'text-[#0A3D91]' : 'text-stone-400' }} hover:text-[#0A3D91]">EN</a>
                    </div>

                    <div>
                        <a href="{{ route('home') }}" class="text-[10px] uppercase tracking-widest text-stone-400 hover:text-stone-700 transition font-extrabold">
                            Exit Wizard &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-stone-200 py-5 flex justify-around items-center z-50 shadow px-4 rounded-t-xl">
        <a href="{{ route('home') }}" class="text-[10px] tracking-widest uppercase font-extrabold text-stone-500 transition">
            Home
        </a>
        <a href="{{ route('booking.index') }}" class="text-[10px] tracking-widest uppercase font-extrabold text-[#0A3D91] transition">
            Book
        </a>
        <a href="{{ route('services.index') }}" class="text-[10px] tracking-widest uppercase font-extrabold text-stone-500 transition">
            Services
        </a>
        <a href="{{ route('outlets.index') }}" class="text-[10px] tracking-widest uppercase font-extrabold text-stone-500 transition">
            Locations
        </a>
    </div>

    <!-- Main Wizard Wrapper -->
    <main class="flex-grow flex items-start justify-center py-12 px-4">
        <div class="w-full max-w-7xl">
            @yield('content')
        </div>
    </main>
</body>
</html>
