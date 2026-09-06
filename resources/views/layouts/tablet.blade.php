@php
    if (request()->has('tablet_outlet_id')) {
        session(['tablet_outlet_id' => (int) request()->query('tablet_outlet_id')]);
    }
    $tabletOutletId = session('tablet_outlet_id', 2);
    $tabletOutlet = \App\Domains\Outlet\Models\Outlet::find($tabletOutletId) ?? \App\Domains\Outlet\Models\Outlet::first();
    $tabletOutletName = $tabletOutlet ? $tabletOutlet->name : 'MORE Hair Studio';
    $allOutlets = \App\Domains\Outlet\Models\Outlet::where('status', 'active')->get();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tablet Operation Terminal | MORE Hair Studio</title>

    <!-- PWA Primary Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#c9512d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MORE Terminal">

    <!-- PWA Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-96.png">
    <link rel="shortcut icon" href="/icons/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/tablet.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="bg-[#fafaf9] text-stone-900 min-h-screen flex flex-col antialiased select-none pb-24 sm:pb-28"
      x-data="tabletMaster()"
      x-init="initMaster()">


    <!-- Top Tablet Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-stone-200 py-3.5 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-40 shadow-xs">
        
        <!-- Left: Logo & Device Status -->
        <div class="flex items-center space-x-3 sm:space-x-4">
            <a href="{{ route('tablet.dashboard') }}" class="flex items-center space-x-3 group">
                <img src="/logo/logo.png" alt="MORE Hair Studio" class="h-8 sm:h-9 w-auto object-contain">
                <div class="flex flex-col border-l border-stone-200 pl-3">
                    <div class="flex items-center space-x-1.5">
                        <span class="text-[9px] uppercase tracking-wider bg-[#faede7] text-[#c9512d] font-extrabold px-2 py-0.5 rounded">Terminal</span>
                    </div>
                    <div class="flex items-center space-x-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] text-stone-400 font-mono font-medium">TABLET-01 &bull; Ready</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Center: Live Clock & Date (Tablet Kiosk Feature) -->
        <div class="hidden md:flex items-center space-x-3 bg-stone-100/70 border border-stone-200/80 px-3.5 py-1.5 rounded-xl font-mono text-xs text-stone-700">
            <svg class="w-3.5 h-3.5 text-[#c9512d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="liveTime" class="font-bold text-stone-900"></span>
            <span class="text-stone-300">|</span>
            <span x-text="liveDate" class="text-stone-500 font-medium"></span>
        </div>

        <!-- Right: Actions, Outlet Selector & Controls -->
        <div class="flex items-center space-x-2 sm:space-x-3">
            
            <!-- Active Outlet Selector -->
            <div class="relative" x-data="{ open: false }">
                <button type="button" 
                        @click="open = !open" 
                        class="flex items-center space-x-2 px-3 py-2 rounded-xl bg-stone-50 border border-stone-200 text-stone-800 text-xs font-bold hover:bg-stone-100 hover:border-stone-300 transition">
                    <svg class="w-3.5 h-3.5 text-[#c9512d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="truncate max-w-[120px] sm:max-w-[180px]">{{ $tabletOutletName }}</span>
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Switcher Dropdown Modal -->
                <div x-show="open" 
                     @click.outside="open = false" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-2 w-64 bg-white border border-stone-200 rounded-2xl shadow-xl p-3 z-50 space-y-2">
                    <div class="text-[10px] font-mono uppercase tracking-widest text-[#c9512d] font-bold px-2 pt-1">
                        Pilih &amp; Kunci Outlet Terminal
                    </div>
                    <div class="space-y-1">
                        @foreach($allOutlets as $out)
                            <a href="{{ request()->fullUrlWithQuery(['tablet_outlet_id' => $out->id]) }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ $tabletOutletId == $out->id ? 'bg-[#faede7] text-[#c9512d] font-bold' : 'text-stone-700 hover:bg-stone-50' }}">
                                <span>{{ $out->name }}</span>
                                @if($tabletOutletId == $out->id)
                                    <svg class="w-4 h-4 text-[#c9512d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Install PWA Button -->
            <button type="button"
                    @click="triggerPwaInstall()"
                    class="flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-white text-xs font-bold transition shadow-xs">
                <svg class="w-3.5 h-3.5 text-[#c9512d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span class="hidden sm:inline">Install PWA</span>
                <span class="sm:hidden">Install</span>
            </button>

            <!-- Fullscreen Kiosk Mode Toggle (Super useful on iPad/Tablet) -->
            <button type="button"
                    @click="toggleFullscreen()"
                    title="Layar Penuh (Kiosk Mode)"
                    class="p-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 transition">
                <template x-if="!isFullscreen">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </template>
                <template x-if="isFullscreen">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </template>
            </button>

            @if(!request()->routeIs('tablet.dashboard'))
                <!-- Back to Dashboard button on sub-pages -->
                <a href="{{ route('tablet.dashboard') }}" 
                   class="flex items-center space-x-1 px-3 py-2 rounded-xl bg-[#faede7] text-[#c9512d] hover:bg-[#c9512d] hover:text-white text-xs font-bold transition">
                    <span>&larr; Menu Utama</span>
                </a>
            @endif

        </div>
    </header>

    <!-- Main Tablet Viewport -->
    <main class="flex-grow flex flex-col p-4 sm:p-8 max-w-7xl mx-auto w-full">
        @yield('content')
    </main>

    <!-- Floating Bottom Navigation Dock for Tablet / iPad -->
    <div class="fixed bottom-3 sm:bottom-6 left-1/2 -translate-x-1/2 z-50 w-[94%] max-w-xl">
        <nav class="tablet-dock rounded-2xl p-1.5 sm:p-2 flex items-center justify-between shadow-2xl backdrop-blur-xl border border-white/10">
            
            <!-- Terminal Home -->
            <a href="{{ route('tablet.dashboard') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-2.5 sm:px-3 rounded-xl transition {{ request()->routeIs('tablet.dashboard') ? 'bg-[#c9512d] text-white font-bold shadow-xs' : 'text-stone-300 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[9px] sm:text-[10px] tracking-tight mt-0.5">Terminal</span>
            </a>

            <!-- Walk-In -->
            <a href="{{ route('tablet.walk-in') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-2.5 sm:px-3 rounded-xl transition {{ request()->routeIs('tablet.walk-in') ? 'bg-[#c9512d] text-white font-bold shadow-xs' : 'text-stone-300 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span class="text-[9px] sm:text-[10px] tracking-tight mt-0.5">Walk-In</span>
            </a>

            <!-- Check-In Scanner -->
            <a href="{{ route('tablet.check-in') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-2.5 sm:px-3 rounded-xl transition {{ request()->routeIs('tablet.check-in') ? 'bg-[#c9512d] text-white font-bold shadow-xs' : 'text-stone-300 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span class="text-[9px] sm:text-[10px] tracking-tight mt-0.5">Scan QR</span>
            </a>

            <!-- Visual Queue -->
            <a href="{{ route('tablet.queue') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-2.5 sm:px-3 rounded-xl transition {{ request()->routeIs('tablet.queue') ? 'bg-[#c9512d] text-white font-bold shadow-xs' : 'text-stone-300 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="text-[9px] sm:text-[10px] tracking-tight mt-0.5">Antrean</span>
            </a>

            <!-- Cashier Monitor -->
            <a href="{{ route('tablet.styscreen') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-2.5 sm:px-3 rounded-xl transition {{ request()->routeIs('tablet.styscreen') ? 'bg-[#c9512d] text-white font-bold shadow-xs' : 'text-stone-300 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="text-[9px] sm:text-[10px] tracking-tight mt-0.5">Kasir</span>
            </a>

            <!-- Stylist Attendance -->
            <a href="{{ route('tablet.attendance') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-2.5 sm:px-3 rounded-xl transition {{ request()->routeIs('tablet.attendance') ? 'bg-[#c9512d] text-white font-bold shadow-xs' : 'text-stone-300 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[9px] sm:text-[10px] tracking-tight mt-0.5">Absen</span>
            </a>

            <!-- Quick Refresh -->
            <button type="button" 
                    @click="window.location.reload()" 
                    title="Muat Ulang Halaman"
                    class="p-2 rounded-xl text-stone-400 hover:text-white hover:bg-white/10 transition hidden sm:flex">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>

        </nav>
    </div>

    <!-- PWA iOS / Safari Guide Modal -->
    <div x-show="showPwaModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-stone-200 text-center space-y-5"
             @click.outside="showPwaModal = false">
            <div class="w-20 h-20 mx-auto rounded-2xl bg-stone-950 p-3 shadow-lg border border-stone-800">
                <img src="/icons/icon-192.png" alt="MORE Icon" class="w-full h-full object-contain">
            </div>

            <div class="space-y-1">
                <h3 class="text-lg font-black text-stone-900 tracking-tight">Pasang MORE Terminal</h3>
                <p class="text-xs text-stone-500 font-medium">Jadikan aplikasi layar penuh di iPad, Tablet, atau Ponsel untuk performa kiosk optimal.</p>
            </div>

            <div class="text-left bg-stone-50 border border-stone-200/80 rounded-2xl p-4 space-y-3 text-xs text-stone-700">
                <div class="flex items-start space-x-3">
                    <span class="w-5 h-5 rounded-full bg-[#faede7] text-[#c9512d] font-bold flex items-center justify-center flex-shrink-0 text-[11px]">1</span>
                    <span>Di browser Safari / Chrome, ketuk tombol <strong>Bagikan / Share</strong> (ikon <svg class="w-3.5 h-3.5 inline text-stone-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg> atau titik tiga).</span>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="w-5 h-5 rounded-full bg-[#faede7] text-[#c9512d] font-bold flex items-center justify-center flex-shrink-0 text-[11px]">2</span>
                    <span>Pilih menu <strong>"Add to Home Screen"</strong> (Tambah ke Layar Utama).</span>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="w-5 h-5 rounded-full bg-[#faede7] text-[#c9512d] font-bold flex items-center justify-center flex-shrink-0 text-[11px]">3</span>
                    <span>Buka dari layar utama tablet untuk menikmati mode Kiosk tanpa address bar.</span>
                </div>
            </div>

            <button type="button" 
                    @click="showPwaModal = false" 
                    class="w-full py-3 bg-[#c9512d] hover:bg-[#a03b1e] text-white text-xs font-bold rounded-xl transition shadow-xs">
                Mengerti &amp; Tutup
            </button>
        </div>
    </div>

    <!-- Master Tablet Script -->
    <script>
    function tabletMaster() {
        return {
            deferredPrompt: null,
            canInstallNative: false,
            showPwaModal: false,
            isFullscreen: false,
            liveTime: '',
            liveDate: '',

            initMaster() {
                // Update live clock
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);

                // Check fullscreen changes
                document.addEventListener('fullscreenchange', () => {
                    this.isFullscreen = Boolean(document.fullscreenElement);
                });

                // Listen for PWA beforeinstallprompt
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.canInstallNative = true;
                });

                window.addEventListener('appinstalled', () => {
                    this.canInstallNative = false;
                    this.deferredPrompt = null;
                });

                // Register service worker
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/sw.js')
                        .then(() => console.log('MORE Tablet PWA v2 Registered'))
                        .catch(err => console.warn('SW registration warning:', err));
                }
            },

            updateClock() {
                const now = new Date();
                this.liveTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                this.liveDate = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
            },

            triggerPwaInstall() {
                if (this.canInstallNative && this.deferredPrompt) {
                    this.deferredPrompt.prompt();
                    this.deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            this.canInstallNative = false;
                        }
                        this.deferredPrompt = null;
                    });
                } else {
                    // Show iOS/Safari instruction modal
                    this.showPwaModal = true;
                }
            },

            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen().catch(err => console.warn(err));
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(err => console.warn(err));
                    }
                }
            }
        };
    }
    </script>
</body>
</html>
