<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Styscreen Login | MORE Hair Studio</title>

    <!-- PWA manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Local Fonts Preload (Suisse Intl) -->
    <link rel="preload" href="/fonts/SuisseIntlTrial-Regular.otf" as="font" type="font/otf" crossorigin>

    <!-- Styles & Scripts -->
    @vite(['resources/css/tablet.css', 'resources/js/app.js'])

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
<body class="bg-stone-50 text-stone-900 min-h-screen flex flex-col justify-center items-center antialiased select-none">
    @yield('content')
</body>
</html>
