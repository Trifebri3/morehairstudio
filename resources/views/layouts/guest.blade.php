<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased selection:bg-brand-500 selection:text-white">
        <div class="min-h-screen flex flex-col justify-between items-center bg-[#fafaf9] relative overflow-hidden py-12 px-4 sm:px-6 lg:px-8">
            <!-- Glowing luxury background effects -->
            <div class="absolute top-[-10%] left-[-15%] w-[60%] h-[50%] bg-gradient-to-br from-brand-100/40 to-transparent rounded-full blur-[140px] pointer-events-none"></div>
            <div class="absolute bottom-[-10%] right-[-15%] w-[60%] h-[50%] bg-gradient-to-tl from-brand-100/40 to-transparent rounded-full blur-[140px] pointer-events-none"></div>
            
            <div class="w-full sm:max-w-md z-10 my-auto">
                <div class="flex justify-center mb-8 transform hover:scale-[1.03] transition-all duration-500 filter drop-shadow-[0_4px_12px_rgba(10,61,145,0.08)]">
                    <a href="/" class="flex flex-col items-center">
                        <x-application-logo class="h-16 w-auto object-contain" />
                    </a>
                </div>

                <div class="w-full bg-white/90 backdrop-blur-md px-8 py-8 shadow-[0_20px_50px_rgba(10,61,145,0.06)] border border-stone-200/50 rounded-2xl">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-stone-400 mt-8 z-10 select-none">
                &copy; {{ date('Y') }} MORE Hair Studio. Hak Cipta Dilindungi.
            </div>
        </div>
    </body>
</html>
