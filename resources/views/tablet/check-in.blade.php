@extends('layouts.tablet')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch h-full py-4 relative">
    <!-- Left Column: Camera Scanner -->
    <div class="glass-panel p-8 rounded-3xl flex flex-col justify-between items-center text-center border-stone-200 bg-white shadow-2xs">
        <div>
            <h3 class="text-xl font-bold text-stone-900 mb-2">Kamera Scanner QR Code</h3>
            <p class="text-stone-500 text-xs leading-relaxed max-w-xs mx-auto">
                Arahkan QR Code tiket customer atau barcode stylist ke arah kamera tablet.
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
            <div class="absolute top-4 left-4 w-4 h-4 border-t-2 border-l-2 border-[#c9512d] z-10"></div>
            <div class="absolute top-4 right-4 w-4 h-4 border-t-2 border-r-2 border-[#c9512d] z-10"></div>
            <div class="absolute bottom-4 left-4 w-4 h-4 border-b-2 border-l-2 border-[#c9512d] z-10"></div>
            <div class="absolute bottom-4 right-4 w-4 h-4 border-b-2 border-r-2 border-[#c9512d] z-10"></div>

            <!-- Glowing laser scanning line -->
            <div class="absolute w-72 h-0.5 bg-[#c9512d]/70 shadow-md shadow-[#c9512d]/80 top-0 left-4 animate-[bounce_3s_infinite] pointer-events-none z-10"></div>

            <!-- Live Video Element -->
            <video x-ref="webcam" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover" x-show="hasWebcam"></video>

            <!-- Fallback text if webcam is disabled/unavailable -->
            <div x-show="!hasWebcam" class="flex flex-col items-center justify-center z-10 px-4">
                <span class="text-stone-400 text-xxs font-bold uppercase tracking-wider text-center">Camera feed loading / denied</span>
            </div>
        </div>

        <div class="w-full mt-6">
            <a href="{{ route('tablet.walk-in') }}" 
               class="w-full py-3.5 px-6 rounded-2xl bg-[#c9512d] hover:bg-[#a03b1e] text-white font-bold text-xs uppercase tracking-widest shadow-md transition-all duration-300 flex items-center justify-center space-x-2">
                <span>Mulai Walk-In Booking Baru</span>
                <span class="text-sm font-extrabold">&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Right Column: Manual code and details -->
    <div class="glass-panel p-8 rounded-3xl flex flex-col justify-between border-stone-200 bg-white">
        <div class="space-y-6">
            <div>
                <h3 class="text-xl font-bold text-stone-900 mb-1">Input Manual Kode Booking</h3>
                <p class="text-stone-500 text-xs">Prefix tanggal otomatis terisi per hari ini. Cukup ketik <strong>5 digit kode unik</strong> customer.</p>
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

            <!-- Code input form with Auto-Updated Daily Date Prefix -->
            @php
                $currentYmd = \Carbon\Carbon::today()->format('ymd');
                $initialUnique = '';
                $initialDate = $currentYmd;
                if (!empty($searchQuery)) {
                    if (preg_match('/(?:MORE|MOR)-(\d{6})-([A-Z0-9]+)/i', $searchQuery, $matches)) {
                        $initialDate = $matches[1];
                        $initialUnique = $matches[2];
                    } else {
                        $initialUnique = $searchQuery;
                    }
                }
            @endphp

            <div x-data="checkInCodeInput({ todayYmd: '{{ $currentYmd }}', initialDate: '{{ $initialDate }}', initialCode: '{{ $initialUnique }}' })" class="space-y-3">
                <label class="block text-[11px] font-mono font-bold uppercase tracking-wider text-stone-700">
                    Kode Booking Customer
                </label>

                <form method="GET" action="{{ route('tablet.check-in') }}" @submit="onSubmit($event)" class="space-y-2.5">
                    <input type="hidden" name="searchQuery" :value="computedFullCode">

                    <!-- Segmented Input: Locked Today Prefix + 5-Char Unique Code + Submit Button -->
                    <div class="flex items-stretch rounded-2xl border-2 border-stone-200 focus-within:border-[#c9512d] bg-white overflow-hidden shadow-2xs transition">
                        
                        <!-- Auto-updating prefix -->
                        <div class="bg-stone-100/90 border-r border-stone-200 px-3 sm:px-4 py-3.5 flex items-center space-x-1.5 select-none text-stone-700 font-mono font-bold text-sm sm:text-base tracking-wider flex-shrink-0">
                            <span class="text-[#c9512d] font-black">MORE</span>-<span x-text="dateYmd"></span>-
                            <span class="text-[8px] uppercase tracking-wider bg-stone-200 text-stone-600 px-1.5 py-0.5 rounded font-extrabold hidden sm:inline" x-text="isToday ? 'Hari Ini' : 'Khusus'"></span>
                        </div>

                        <!-- 5-character input -->
                        <input type="text" 
                               x-ref="uniqueInput"
                               x-model="uniqueCode"
                               @input="handleInput($event)"
                               @paste="handlePaste($event)"
                               placeholder="GDKYS" 
                               maxlength="18"
                               autocapitalize="characters"
                               autocomplete="off"
                               spellcheck="false"
                               class="flex-1 min-w-0 px-3 sm:px-4 py-3 text-base sm:text-lg font-mono font-black uppercase tracking-widest text-stone-900 placeholder:text-stone-300 focus:outline-none bg-transparent" />

                        <!-- Submit action -->
                        <button type="submit" 
                                :disabled="!uniqueCode.trim()"
                                class="px-5 sm:px-6 py-3 bg-[#c9512d] hover:bg-[#a03b1e] disabled:bg-stone-200 disabled:text-stone-400 disabled:cursor-not-allowed text-white font-bold text-xs sm:text-sm tracking-wide transition flex items-center space-x-1.5 flex-shrink-0 cursor-pointer">
                            <span>Cari</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>

                    <!-- Helper Info & Date Modifier -->
                    <div class="flex items-center justify-between text-[11px] text-stone-400 font-medium px-1">
                        <span>Format: <strong>MORE-<span x-text="dateYmd"></span>-[5 Karakter]</strong></span>
                        <button type="button" 
                                @click="showCustomDate = !showCustomDate" 
                                class="text-[#c9512d] font-bold hover:underline">
                            <span x-text="showCustomDate ? 'Kembali ke Hari Ini' : 'Booking Tanggal Lain?'"></span>
                        </button>
                    </div>

                    <!-- Optional Date Modifier for non-today bookings -->
                    <div x-show="showCustomDate" 
                         x-transition 
                         class="bg-stone-50 border border-stone-200 rounded-xl p-3 space-y-2 text-xs">
                        <label class="block text-stone-600 font-bold text-[11px]">Pilih Tanggal Reservasi Customer:</label>
                        <div class="flex items-center space-x-2">
                            <input type="date" 
                                   x-model="customDateInput" 
                                   @change="onDateChanged()"
                                   class="px-3 py-1.5 bg-white border border-stone-200 rounded-lg text-xs font-mono text-stone-800 focus:outline-none focus:border-[#c9512d]" />
                            <span class="text-stone-500 font-mono text-[11px]">Prefix menjadi: MORE-<span class="font-bold text-stone-900" x-text="dateYmd"></span>-</span>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Booking details if found -->
            @if($booking)
                <div class="border border-[#faede7] bg-stone-50 rounded-2xl p-6 space-y-4">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#c9512d] border-b border-stone-200 pb-2">
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

<script>
function checkInCodeInput(config) {
    const initDate = config.initialDate || config.todayYmd;
    let initDateInput = new Date().toISOString().split('T')[0];
    if (initDate && initDate.length === 6) {
        initDateInput = `20${initDate.slice(0, 2)}-${initDate.slice(2, 4)}-${initDate.slice(4, 6)}`;
    }

    return {
        todayYmd: config.todayYmd,
        dateYmd: initDate,
        uniqueCode: config.initialCode || '',
        showCustomDate: (initDate !== config.todayYmd),
        customDateInput: initDateInput,

        get isToday() {
            return this.dateYmd === this.todayYmd;
        },

        get computedFullCode() {
            const clean = (this.uniqueCode || '').trim().toUpperCase();
            if (!clean) return '';
            // If user pasted or typed full code with prefix
            if (clean.startsWith('MORE-') || clean.startsWith('MOR-')) {
                return clean;
            }
            return `MORE-${this.dateYmd}-${clean}`;
        },

        handleInput(e) {
            let val = e.target.value.toUpperCase();
            // If user typed/pasted full code starting with MORE- or MOR-
            const match = val.match(/^(?:MORE|MOR)-(\d{6})-([A-Z0-9]+)$/);
            if (match) {
                this.dateYmd = match[1];
                this.uniqueCode = match[2];
                return;
            }
            this.uniqueCode = val.replace(/[^A-Z0-9]/g, '');
        },

        handlePaste(e) {
            setTimeout(() => {
                let val = (this.uniqueCode || '').toUpperCase();
                const match = val.match(/(?:MORE|MOR)-(\d{6})-([A-Z0-9]+)/);
                if (match) {
                    this.dateYmd = match[1];
                    this.uniqueCode = match[2];
                }
            }, 10);
        },

        onDateChanged() {
            if (!this.customDateInput) {
                this.dateYmd = this.todayYmd;
                return;
            }
            const parts = this.customDateInput.split('-');
            if (parts.length === 3) {
                const yy = parts[0].slice(-2);
                const mm = parts[1];
                const dd = parts[2];
                this.dateYmd = `${yy}${mm}${dd}`;
            }
        },

        onSubmit(e) {
            // allow default GET submission with computedFullCode
        }
    };
}
</script>
@endsection
