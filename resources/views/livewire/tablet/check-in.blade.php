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

        <div class="w-full mt-6">
            <button onclick="window.location.href='{{ route('tablet.walk-in') }}'" 
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-black text-xs uppercase tracking-widest shadow-lg hover:shadow-blue-500/25 transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 select-none focus:outline-none flex items-center justify-center space-x-2">
                <span>Mulai Walk-In Booking Baru</span>
                <span class="text-sm font-extrabold">&rarr;</span>
            </button>
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
            <div class="flex space-x-3 items-end">
                <div class="flex-grow">
                    <x-ui.input label="Kode Booking" placeholder="e.g. MOR-180826-A1B2C" wire:model.defer="searchQuery" />
                </div>
                <x-ui.button variant="primary" wire:click="search" class="h-[48px] rounded-lg">
                    Cari
                </x-ui.button>
            </div>

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
                            <span class="font-bold text-stone-850 block mt-0.5">{{ $booking->items->first()->service->name }}</span>
                        </div>
                        <div>
                            <span class="text-stone-400 block font-medium">Stylist</span>
                            <span class="font-bold text-stone-850 block mt-0.5">{{ $booking->stylist->name }}</span>
                        </div>
                        <div>
                            <span class="text-stone-400 block font-medium">Tanggal / Jam</span>
                            <span class="font-bold text-stone-850 block mt-0.5">{{ $booking->booking_date->format('d M Y') }} @ {{ substr($booking->items->first()->start_time, 0, 5) }}</span>
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
                        <x-ui.button variant="primary" size="sm" wire:click="processCheckIn">
                            Check-In Sekarang
                        </x-ui.button>
                    </div>
                </div>
            @endif
        </div>

        <div class="pt-6 border-t border-stone-200 flex justify-end">
            <x-ui.button variant="secondary" size="md" onclick="window.location.href='{{ route('tablet.dashboard') }}'">
                Kembali
            </x-ui.button>
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

    <!-- Overlay Manager Wrapper with wire:ignore to prevent Livewire DOM state resets -->
    <div wire:ignore
         x-data="{
            showWalkin: false,
            showCheckin: false,
            showAbsen: false,
            msg: ''
         }"
         x-init="
            // Handle Walk-In Redirect Session status
            const sessionStatus = '{{ session('status', '') }}';
            if (sessionStatus && (sessionStatus.indexOf('Walk-In') !== -1 || sessionStatus.indexOf('berhasil dibuat') !== -1)) {
                showWalkin = true;
                msg = sessionStatus;
                setTimeout(function() { showWalkin = false; }, 5000);
            }
         }"
         x-on:show-my-overlay.window="
            let detail = $event.detail;
            msg = detail.message || '';
            const type = detail.type || '';
            
            if (type === 'checkin') {
                showCheckin = true;
                setTimeout(function() { showCheckin = false; $wire.set('successMessage', null); }, 5000);
            } else if (type === 'absen') {
                showAbsen = true;
                setTimeout(function() { showAbsen = false; $wire.set('successMessage', null); }, 5000);
            } else if (type === 'walkin') {
                showWalkin = true;
                setTimeout(function() { showWalkin = false; }, 5000);
            }
         }">
         
        <!-- OVERLAY 1: WALKIN BOOKING SUCCESS (Large Indigo Theme) -->
        <div x-show="showWalkin" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 scale-100" 
             x-transition:leave-end="opacity-0 scale-95" 
             class="fixed inset-0 z-[9999] flex items-center justify-center p-6 bg-gradient-to-tr from-[#020617] via-[#0b1329] to-[#1e3a8a] text-white">
            
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(59,130,246,0.15)_0%,transparent_80%)] pointer-events-none"></div>
            
            <div class="relative z-10 text-center space-y-8 max-w-2xl mx-auto px-4">
                <!-- Extremely Large Pulsing Circle -->
                <div class="relative w-36 h-36 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto border border-blue-400/40 shadow-[0_0_50px_rgba(59,130,246,0.3)] animate-pulse">
                    <div class="absolute inset-0 rounded-full border-4 border-blue-400/20" style="animation: pulseOutline 2s infinite;"></div>
                    <svg class="w-16 h-16 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                
                <div class="space-y-4">
                    <span class="text-sm font-black uppercase tracking-widest text-blue-400 block animate-[bounce_2s_infinite]">WALK-IN SUCCESSFUL</span>
                    <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-tighter leading-none bg-gradient-to-r from-white via-blue-100 to-blue-300 bg-clip-text text-transparent">
                        WALK-IN BERHASIL!
                    </h1>
                    <p class="text-lg sm:text-xl text-blue-100 max-w-xl mx-auto font-light leading-relaxed" x-text="msg"></p>
                </div>
                
                <!-- Countdown Bar -->
                <div class="w-64 h-1.5 bg-white/10 rounded-full mx-auto overflow-hidden shadow-inner">
                    <div class="h-full bg-blue-500 shadow-[0_0_10px_#3b82f6]" style="animation: loadingBar 5s linear forwards;"></div>
                </div>
                
                <span class="text-[10px] text-blue-300 uppercase tracking-widest block font-bold">Kembali ke Kiosk dalam beberapa detik...</span>
            </div>
        </div>

        <!-- OVERLAY 2: CUSTOMER CHECK-IN (Large Emerald Green Theme) -->
        <div x-show="showCheckin" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 scale-100" 
             x-transition:leave-end="opacity-0 scale-95" 
             class="fixed inset-0 z-[9999] flex items-center justify-center p-6 bg-gradient-to-tr from-[#022c22] via-[#064e3b] to-[#14532d] text-white">
            
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(52,211,153,0.15)_0%,transparent_80%)] pointer-events-none"></div>
            
            <div class="relative z-10 text-center space-y-8 max-w-2xl mx-auto px-4">
                <!-- Extremely Large Bouncing Checkmark Ring -->
                <div class="relative w-36 h-36 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto border border-emerald-400/40 shadow-[0_0_50px_rgba(52,211,153,0.3)] animate-bounce">
                    <div class="absolute inset-0 rounded-full border-4 border-emerald-400/20" style="animation: pulseOutline 2s infinite;"></div>
                    <svg class="w-18 h-18 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <div class="space-y-4">
                    <span class="text-sm font-black uppercase tracking-widest text-emerald-400 block">CHECK-IN CONFIRMED</span>
                    <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-tighter leading-none bg-gradient-to-r from-white via-emerald-100 to-emerald-300 bg-clip-text text-transparent">
                        CHECK-IN BERHASIL!
                    </h1>
                    <p class="text-lg sm:text-xl text-emerald-100 max-w-xl mx-auto font-light leading-relaxed" x-text="msg"></p>
                </div>
                
                <!-- Countdown Bar -->
                <div class="w-64 h-1.5 bg-white/10 rounded-full mx-auto overflow-hidden shadow-inner">
                    <div class="h-full bg-emerald-500 shadow-[0_0_10px_#10b981]" style="animation: loadingBar 5s linear forwards;"></div>
                </div>
                
                <span class="text-[10px] text-emerald-300 uppercase tracking-widest block font-bold">Kembali ke Kiosk dalam beberapa detik...</span>
            </div>
        </div>

        <!-- OVERLAY 3: HAIRSTYLIST ATTENDANCE (Large Amber Gold Theme) -->
        <div x-show="showAbsen" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 scale-100" 
             x-transition:leave-end="opacity-0 scale-95" 
             class="fixed inset-0 z-[9999] flex items-center justify-center p-6 bg-gradient-to-tr from-[#1c1917] via-[#292524] to-[#78350f] text-white">
            
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(245,158,11,0.15)_0%,transparent_80%)] pointer-events-none"></div>
            
            <div class="relative z-10 text-center space-y-8 max-w-2xl mx-auto px-4">
                <!-- Extremely Large Laser Scanner Box -->
                <div class="relative w-36 h-36 bg-amber-500/20 rounded-2xl flex items-center justify-center mx-auto border border-amber-400/40 shadow-[0_0_50px_rgba(245,158,11,0.3)] overflow-hidden">
                    <div class="absolute w-full h-1 bg-amber-400 shadow-[0_0_15px_#f59e0b] top-0 left-0" style="animation: scannerBeam 2.5s infinite;"></div>
                    <svg class="w-16 h-16 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <div class="space-y-4">
                    <span class="text-sm font-black uppercase tracking-widest text-amber-400 block">HAIRSTYLIST SYSTEM CLOCK</span>
                    <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-tighter leading-none bg-gradient-to-r from-white via-amber-100 to-amber-300 bg-clip-text text-transparent">
                        ABSENSI BERHASIL!
                    </h1>
                    <p class="text-lg sm:text-xl text-amber-100 max-w-xl mx-auto font-light leading-relaxed" x-text="msg"></p>
                </div>
                
                <!-- Countdown Bar -->
                <div class="w-64 h-1.5 bg-white/10 rounded-full mx-auto overflow-hidden shadow-inner">
                    <div class="h-full bg-amber-500 shadow-[0_0_10px_#f59e0b]" style="animation: loadingBar 5s linear forwards;"></div>
                </div>
                
                <span class="text-[10px] text-amber-300 uppercase tracking-widest block font-bold">Kembali ke Kiosk dalam beberapa detik...</span>
            </div>
        </div>
    </div>

    <!-- Livewire v3 JS Bridge to Alpine overlay -->
    <script>
        document.addEventListener('livewire:init', function() {
            Livewire.on('show-success-overlay', function(event) {
                let payload = event;
                if (Array.isArray(event)) {
                    payload = event[0] || {};
                }
                
                const type = payload.type || '';
                const message = payload.message || '';
                
                // Dispatch event to window that Alpine.js wire:ignore block can listen to
                const customEvent = new CustomEvent('show-my-overlay', {
                    detail: { type: type, message: message }
                });
                window.dispatchEvent(customEvent);
            });
        });
    </script>
</div>
