@php
    if (request()->has('tablet_outlet_id')) {
        session(['tablet_outlet_id' => (int) request()->query('tablet_outlet_id')]);
    }
    $tabletOutletId = session('tablet_outlet_id', 1);
    $tabletOutlet = \App\Domains\Outlet\Models\Outlet::find($tabletOutletId);
    $tabletOutletName = $tabletOutlet ? $tabletOutlet->name : 'Studio';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tablet Operation | MORE Hair Studio</title>

    <!-- PWA manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles & Scripts -->
    @vite(['resources/css/tablet.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen flex flex-col antialiased select-none">
    @if(session()->has('impersonator_id'))
        <div class="bg-blue-600 text-white text-xs py-3 px-8 flex justify-between items-center z-50 select-none">
            <div>
                <span>Anda sedang menguji sistem sebagai <strong>{{ auth()->user()->name }}</strong> (Role: {{ auth()->user()->role }}).</span>
            </div>
            <a href="{{ route('impersonate.stop') }}" class="bg-white text-blue-600 px-3.5 py-1.5 rounded font-extrabold hover:bg-stone-100 transition shadow-sm">
                Kembali ke Super Admin
            </a>
        </div>
    @endif

    <!-- Header -->
    <header class="bg-white border-b border-stone-200 py-5 px-8 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-2xl font-black font-serif tracking-widest text-[#0A3D91]">MORE</span>
            <span class="text-xs uppercase tracking-widest text-stone-400 border-l border-stone-200 pl-3">Tablet Panel</span>
        </div>
        <div class="flex items-center space-x-6" 
             x-data="{ deferredPrompt: null, showInstallBtn: false }"
             x-init="window.addEventListener('beforeinstallprompt', (e) => {
                 e.preventDefault();
                 deferredPrompt = e;
                 showInstallBtn = true;
             });
             window.addEventListener('appinstalled', () => {
                 showInstallBtn = false;
                 deferredPrompt = null;
             });">
            
            <!-- Active Outlet Selector with Hidden Toggler -->
            <div x-data="{ showSwitcher: false }" class="text-xs uppercase text-[#0A3D91] font-bold tracking-widest flex items-center space-x-2 relative z-50">
                <span>Active Outlet:</span>
                <span @click="showSwitcher = !showSwitcher" class="bg-blue-55 text-[#0A3D91] px-3 py-1 rounded border border-blue-100 font-mono cursor-pointer hover:bg-blue-100 transition select-none">
                    {{ $tabletOutletName }}
                </span>
                
                <!-- Hidden Switcher Form -->
                <div x-show="showSwitcher" 
                     @click.outside="showSwitcher = false"
                     class="absolute top-8 right-0 bg-white border border-stone-200 p-4 rounded-xl shadow-lg min-w-[220px]"
                     style="display: none;">
                    <p class="text-[9px] uppercase tracking-wider text-stone-400 font-mono mb-2">Switch & Lock Outlet</p>
                    <form method="GET" action="{{ url()->current() }}" class="space-y-2">
                        <select name="tablet_outlet_id" onchange="this.form.submit()" class="w-full text-xs border border-stone-200 rounded-lg p-2 bg-stone-50 text-stone-900 font-mono focus:outline-none">
                            @foreach(\App\Domains\Outlet\Models\Outlet::where('status', 'active')->get() as $out)
                                <option value="{{ $out->id }}" {{ $tabletOutletId == $out->id ? 'selected' : '' }}>
                                    {{ $out->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            
            <!-- PWA Install Button -->
            <button x-show="showInstallBtn"
                    @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then((choice) => { if(choice.outcome === 'accepted') { showInstallBtn = false; } })"
                    class="text-[10px] uppercase tracking-wider bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-1.5 rounded font-extrabold shadow-sm transition duration-150"
                    style="display: none;">
                Install App
            </button>

            <a href="{{ route('tablet.dashboard') }}" class="text-xs uppercase tracking-widest text-stone-500 hover:text-blue-600 transition font-extrabold">
                Menu Utama
            </a>
        </div>
    </header>

    <!-- Main Viewport -->
    <main class="flex-grow flex flex-col p-8 justify-center items-center">
        <div class="w-full max-w-5xl h-full flex flex-col">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer Status -->
    <footer class="bg-white border-t border-stone-200 py-4 px-8 flex items-center justify-between text-xs text-stone-400">
        <div>Device ID: TABLET-OUTLET-01</div>
        <div>System status: Online | Camera Ready</div>
    </footer>

    <!-- Service worker registration -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(() => console.log('MORE Tablet PWA Service Worker Registered'));
        }
    </script>

</body>
</html>
