@extends('layouts.tablet')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch h-full py-4 relative">
    <!-- Left Column: Camera Scanner simulator -->
    <div class="glass-panel p-8 rounded-3xl flex flex-col justify-between items-center text-center border-stone-200 bg-white">
        <div>
            <h3 class="text-xl font-bold text-stone-900 mb-2">Simulasi QR Scanner</h3>
            <p class="text-stone-500 text-xs leading-relaxed max-w-xs mx-auto">
                Scan QR Code yang tertera pada invoice customer langsung dari layar HP mereka.
            </p>
        </div>

        <!-- enlarged pulsing scanner frame with active HTML5 webcam -->
        <div class="my-6 relative w-80 h-80 border-2 border-stone-250 bg-stone-50 flex flex-col items-center justify-center rounded-2xl overflow-hidden"
             x-data="{ hasWebcam: false, initWebcam() {
                 navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                     .then(stream => {
                         this.$refs.webcam.srcObject = stream;
                         this.hasWebcam = true;
                     })
                     .catch(err => {
                         console.warn('Webcam access not allowed or camera is unavailable:', err);
                     });
             } }"
             x-init="initWebcam()">
            <!-- scanner corner guides -->
            <div class="absolute top-4 left-4 w-4 h-4 border-t-2 border-l-2 border-blue-600 z-10"></div>
            <div class="absolute top-4 right-4 w-4 h-4 border-t-2 border-r-2 border-blue-600 z-10"></div>
            <div class="absolute bottom-4 left-4 w-4 h-4 border-b-2 border-l-2 border-blue-600 z-10"></div>
            <div class="absolute bottom-4 right-4 w-4 h-4 border-b-2 border-r-2 border-blue-600 z-10"></div>

            <!-- Glowing laser scanning line -->
            <div class="absolute w-72 h-0.5 bg-blue-500/50 shadow-md shadow-blue-500/80 top-0 left-4 animate-[bounce_3s_infinite] pointer-events-none z-10"></div>

            <!-- Live Video Element -->
            <video x-ref="webcam" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover" x-show="hasWebcam"></video>

            <!-- Fallback text if webcam is disabled/unavailable -->
            <div x-show="!hasWebcam" class="flex flex-col items-center justify-center z-10 px-4">
                <span class="text-stone-400 text-xxs font-bold uppercase tracking-wider text-center">Camera feed loading / denied</span>
            </div>
        </div>

        <div class="w-full mt-6 space-y-3">
            <a href="?searchQuery=MOR-180826-A1B2C" class="block w-full py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-[10px] font-bold uppercase tracking-widest rounded-xl transition">
                Simulasikan Scan QR Code
            </a>
            <a href="{{ route('tablet.walk-in') }}" 
               class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-black text-xs uppercase tracking-widest shadow-lg hover:shadow-blue-500/25 transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center space-x-2">
                <span>Mulai Walk-In Booking Baru</span>
                <span class="text-sm font-extrabold">&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Right Column: Manual code and details -->
    <div class="glass-panel p-8 rounded-3xl flex flex-col justify-between border-stone-200 bg-white">
        <div class="space-y-6">
            <div>
                <h3 class="text-xl font-bold text-stone-900 mb-2">Input Manual</h3>
                <p class="text-stone-500 text-xs">Masukkan kode booking atau token unik secara manual</p>
            </div>

            <!-- Alerts -->
            @if($errorMessage)
                <x-ui.alert variant="danger">
                    {{ $errorMessage }}
                </x-ui.alert>
            @endif

            @if($successMessage)
                <x-ui.alert variant="success">
                    {{ $successMessage }}
                </x-ui.alert>
            @endif

            @if(session('status'))
                <x-ui.alert variant="success">
                    {{ session('status') }}
                </x-ui.alert>
            @endif

            <!-- Code input form -->
            <form method="GET" action="{{ route('tablet.check-in') }}" class="flex space-x-3 items-end">
                <div class="flex-grow">
                    <x-ui.input label="Kode Booking" name="searchQuery" placeholder="e.g. MOR-180826-A1B2C" value="{{ $searchQuery }}" />
                </div>
                <x-ui.button variant="primary" type="submit" class="h-[48px] rounded-lg">
                    Cari
                </x-ui.button>
            </form>

            <!-- Booking details if found -->
            @if($booking)
                <div class="border border-blue-100 bg-stone-50 rounded-2xl p-6 space-y-4">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-blue-600 border-b border-stone-200 pb-2">
                        Data Booking Ditemukan
                    </h4>
                    
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-stone-400 block font-medium">Customer</span>
                            <span class="font-bold text-stone-850 block mt-0.5">{{ $booking->customer->name }}</span>
                        </div>
                        <div>
                            <span class="text-stone-400 block font-medium">WhatsApp</span>
                            <span class="font-bold text-stone-850 block mt-0.5">{{ $booking->customer->phone }}</span>
                        </div>
                        <div>
                            <span class="text-stone-400 block font-medium">Layanan</span>
                            <span class="font-bold text-stone-850 block mt-0.5">{{ $booking->items->first()?->service?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-stone-400 block font-medium">Stylist</span>
                            <span class="font-bold text-stone-850 block mt-0.5">{{ $booking->stylist?->name ?? 'Any Stylist' }}</span>
                        </div>
                        <div>
                            <span class="text-stone-400 block font-medium">Tanggal / Jam</span>
                            <span class="font-bold text-stone-850 block mt-0.5">{{ $booking->booking_date->format('d M Y') }} @ {{ substr($booking->items->first()?->start_time ?? '00:00:00', 0, 5) }}</span>
                        </div>
                        <div>
                            <span class="text-stone-400 block font-medium">Status Booking</span>
                            <x-ui.badge variant="{{ $booking->status === 'confirmed' ? 'success' : 'neutral' }}" class="mt-0.5">
                                {{ $booking->status }}
                            </x-ui.badge>
                        </div>
                    </div>

                    <div class="border-t border-stone-200 pt-4 flex justify-between items-center">
                        <span class="font-bold font-mono text-stone-900 text-sm">
                            Rp {{ number_format($booking->net_amount, 0, ',', '.') }}
                        </span>
                        <form method="POST" action="{{ route('tablet.check-in.process', $booking->id) }}">
                            @csrf
                            <x-ui.button variant="primary" size="sm" type="submit">
                                Check-In Sekarang
                            </x-ui.button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="pt-6 border-t border-stone-200 flex justify-end">
            <a href="{{ route('tablet.dashboard') }}" class="px-4 py-2 border border-stone-200 rounded-xl text-stone-650 hover:bg-stone-50 text-xs font-bold transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Keyframe Styles for Animation -->
    <style>
    @keyframes loadingBar {
        from { width: 0%; }
        to { width: 100%; }
    }
    @keyframes scannerBeam {
        0% { top: 0%; }
        50% { top: 100%; }
        100% { top: 0%; }
    }
    @keyframes pulseOutline {
        0% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0; }
        100% { transform: scale(1); opacity: 0.5; }
    }
    </style>

    @if(session()->has('success_overlay'))
        @php 
            $overlay = session('success_overlay'); 
            $isAbsen = ($overlay['type'] === 'absen');
            $bgGradient = $isAbsen 
                ? 'from-[#1c1917] via-[#292524] to-[#78350f]' 
                : 'from-[#022c22] via-[#064e3b] to-[#14532d]';
            $strokeColor = $isAbsen ? 'text-amber-400' : 'text-emerald-400';
            $glowingRing = $isAbsen 
                ? 'bg-amber-500/20 border-amber-400/40 shadow-[0_0_50px_rgba(245,158,11,0.3)]' 
                : 'bg-emerald-500/20 border-emerald-400/40 shadow-[0_0_50px_rgba(52,211,153,0.3)]';
        @endphp
        
        <div id="success-overlay" class="fixed inset-0 z-[9999] flex items-center justify-center p-6 bg-gradient-to-tr {{ $bgGradient }} text-white">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(52,211,153,0.15)_0%,transparent_80%)] pointer-events-none"></div>
            
            <div class="relative z-10 text-center space-y-8 max-w-2xl mx-auto px-4">
                <div class="relative w-36 h-36 rounded-full flex items-center justify-center mx-auto border {{ $glowingRing }} animate-bounce">
                    <div class="absolute inset-0 rounded-full border-4 border-current opacity-20" style="animation: pulseOutline 2s infinite;"></div>
                    @if($isAbsen)
                        <svg class="w-16 h-16 {{ $strokeColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-18 h-18 {{ $strokeColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                </div>
                
                <div class="space-y-4">
                    <span class="text-sm font-black uppercase tracking-widest text-emerald-400 block">{{ $isAbsen ? 'ATTENDANCE CONFIRMED' : 'CHECK-IN CONFIRMED' }}</span>
                    <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-tighter leading-none bg-gradient-to-r from-white via-emerald-100 to-emerald-300 bg-clip-text text-transparent">
                        {{ $isAbsen ? 'ABSENSI BERHASIL!' : 'CHECK-IN BERHASIL!' }}
                    </h1>
                    <p class="text-lg sm:text-xl text-emerald-100 max-w-xl mx-auto font-light leading-relaxed">{{ $overlay['message'] }}</p>
                </div>
                
                <!-- Countdown Bar -->
                <div class="w-64 h-1.5 bg-white/10 rounded-full mx-auto overflow-hidden shadow-inner">
                    <div class="h-full bg-emerald-50 shadow-[0_0_10px_#10b981]" style="animation: loadingBar 5s linear forwards;"></div>
                </div>
                
                <span class="text-[10px] text-emerald-350 uppercase tracking-widest block font-bold">Kembali ke Kiosk dalam beberapa detik...</span>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const el = document.getElementById('success-overlay');
                if (el) el.remove();
            }, 5000);
        </script>
    @endif
</div>
@endsection
