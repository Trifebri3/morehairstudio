<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MORE Hair Studio') }}</title>

    <!-- Local Fonts Preload (Suisse Intl) -->
    <link rel="preload" href="/fonts/SuisseIntlTrial-Regular.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlTrial-Medium.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlTrial-Bold.otf" as="font" type="font/otf" crossorigin>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- ═══ FONT PRELOAD — Stack Sans Notch & Suisse Intl ═══ -->
    <link rel="preload" href="/fonts/StackSansNotch-VariableFont_wght.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlTrial-Regular.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/SuisseIntlMonoTrial-Regular.otf" as="font" type="font/otf" crossorigin>
    <!-- ═══ INLINE FONT-FACE (bulletproof) ═══ -->
    <style>
        @font-face { font-family: 'Stack Sans Notch'; src: url('/fonts/StackSansNotch-VariableFont_wght.ttf') format('truetype'); font-weight: 100 900; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Suisse Intl'; src: url('/fonts/SuisseIntlTrial-Regular.otf') format('opentype'); font-weight: 400; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Suisse Intl'; src: url('/fonts/SuisseIntlTrial-Medium.otf') format('opentype'); font-weight: 500; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Suisse Intl'; src: url('/fonts/SuisseIntlTrial-Bold.otf') format('opentype'); font-weight: 700; font-style: normal; font-display: swap; }
        @font-face { font-family: 'SuisseIntlMono'; src: url('/fonts/SuisseIntlMonoTrial-Regular.otf') format('opentype'); font-weight: 400; font-style: normal; font-display: swap; }
        html, body, p, span, div, li, td, th, label, a, input, button, select, textarea { font-family: 'Suisse Intl', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important; }
        h1, h2, h3, h4, h5, h6, .font-display, .font-headline, [class*="text-2xl"], [class*="text-3xl"], [class*="text-4xl"], [class*="text-5xl"], [class*="text-6xl"], [class*="text-7xl"], [class*="text-8xl"], [class*="text-9xl"] { font-family: 'Stack Sans Notch', -apple-system, BlinkMacSystemFont, sans-serif !important; letter-spacing: -0.02em; }
        .font-mono, code, kbd, pre, samp { font-family: 'SuisseIntlMono', 'Courier New', monospace !important; letter-spacing: 0; }
    </style>    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-stone-50">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation Header -->
        <nav class="bg-white border-b border-stone-200" x-data="{ mobileMenuOpen: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="flex items-center">
                                <img src="/logo/logo.png?v=3" alt="MORE Hair Studio" class="h-8 w-auto object-contain">
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Navigation Links -->
                        <div class="hidden sm:flex sm:items-center sm:space-x-6 text-sm font-medium text-stone-600">
                            <a href="{{ route('dashboard') }}" class="hover:text-stone-900">Dashboard</a>
                            <a href="{{ route('profile') }}" class="hover:text-stone-900">Profil</a>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="flex items-center text-sm font-medium text-stone-650 hover:text-stone-900 focus:outline-none transition">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" 
                                 style="display: none;">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-stone-700 hover:bg-stone-100">Profil Saya</a>
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-stone-700 hover:bg-stone-100">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow-sm border-b border-stone-200">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="flex-grow py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
