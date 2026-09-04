{{-- Interactive Guide & Live Demo Walkthrough Component --}}
<div x-data="moreInteractiveGuide()" 
     x-init="initGuide()" 
     @open-interactive-guide.window="openMenu = true"
     @start-interactive-tour.window="startDemo($event.detail)"
     class="select-none font-sans" 
     x-cloak>
    
    <!-- Super Small Floating Guide Launcher Button (Just 'i' Icon Only) -->
    <div class="fixed bottom-5 right-5 z-40">
        <button type="button"
                @click="openMenu = true"
                aria-label="Panduan"
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#171615]/95 hover:bg-[#c9512d] text-stone-300 hover:text-white flex items-center justify-center shadow-lg border border-stone-700/80 hover:border-[#c9512d] transition-all duration-200 transform hover:scale-110 active:scale-95 font-serif italic text-xs sm:text-sm font-bold select-none cursor-pointer">
            i
        </button>
    </div>

    <!-- Modal Pilihan Tutorial -->
    <div x-show="openMenu" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div @click.away="openMenu = false"
             class="bg-[#171615] text-[#eae8e5] border border-stone-800 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative space-y-6 overflow-hidden">
            
            <!-- Ambient Glow -->
            <div class="absolute -top-24 -right-24 w-60 h-60 bg-[#c9512d]/20 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Header -->
            <div class="flex items-start justify-between relative z-10 border-b border-stone-800/80 pb-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-[#c9512d]/10 border border-[#c9512d]/25 text-[#c9512d] text-[10px] font-mono font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#c9512d] animate-pulse"></span>
                        <span>Interactive Live Demo</span>
                    </div>
                    <h3 class="text-xl font-black font-headline tracking-tight text-white uppercase">
                        Panduan Interaktif Pengguna
                    </h3>
                    <p class="text-xs text-stone-400 font-light leading-relaxed">
                        Pilih tutorial di bawah untuk melihat animasi kursor virtual yang memandu dan mendemonstrasikan sistem secara langsung.
                    </p>
                </div>
                <button type="button" @click="openMenu = false" class="p-2 rounded-full text-stone-400 hover:text-white hover:bg-stone-800/60 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Two Tutorial Cards -->
            <div class="grid grid-cols-1 gap-4 relative z-10">
                
                <!-- Tutorial 1: Booking Online -->
                <div class="group p-5 rounded-2xl bg-stone-900/80 hover:bg-stone-900 border border-stone-800 hover:border-[#c9512d]/50 transition duration-300 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer"
                     @click="startDemo('booking')">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#c9512d]/10 border border-[#c9512d]/20 flex items-center justify-center text-[#c9512d] shrink-0 group-hover:scale-105 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-sm text-white group-hover:text-[#c9512d] transition">
                                    Cara Booking Pengalaman MORE
                                </h4>
                                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-[#c9512d] text-white">4 Tahap</span>
                            </div>
                            <p class="text-xs text-stone-400 font-light leading-relaxed">
                                Simulasi kursor memilih Treatment, Hair Artist favorit, jam sesi kosong, hingga penerbitan E-Ticket digital.
                            </p>
                        </div>
                    </div>
                    <button type="button" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#c9512d] hover:bg-[#b04322] text-white text-xs font-mono font-bold uppercase tracking-wider transition shrink-0 gap-1.5 shadow-md">
                        <span>Mulai Demo</span>
                        <span>&rarr;</span>
                    </button>
                </div>

                <!-- Tutorial 2: Check-In Tablet -->
                <div class="group p-5 rounded-2xl bg-stone-900/80 hover:bg-stone-900 border border-stone-800 hover:border-[#c9512d]/50 transition duration-300 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer"
                     @click="startDemo('checkin')">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#c9512d]/10 border border-[#c9512d]/20 flex items-center justify-center text-[#c9512d] shrink-0 group-hover:scale-105 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-sm text-white group-hover:text-[#c9512d] transition">
                                    Cara Check-In di Tablet Kiosk
                                </h4>
                                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-stone-800 text-stone-300">Cepat</span>
                            </div>
                            <p class="text-xs text-stone-400 font-light leading-relaxed">
                                Simulasi saat tiba di studio: scan QR Code E-Ticket atau cukup ketik 5 digit kode unik tanpa awalan panjang.
                            </p>
                        </div>
                    </div>
                    <button type="button" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-stone-800 hover:bg-[#c9512d] text-white text-xs font-mono font-bold uppercase tracking-wider transition shrink-0 gap-1.5 shadow-md">
                        <span>Mulai Demo</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>

            <!-- Footer / Direct Links -->
            <div class="pt-4 border-t border-stone-800/80 flex flex-wrap items-center justify-between gap-3 text-xs font-mono text-stone-500">
                <span class="text-[11px]">Bisa dijeda atau dikontrol secara manual sewaktu-waktu.</span>
                <div class="flex gap-4">
                    <a href="{{ route('booking.index') }}" class="text-stone-300 hover:text-[#c9512d] underline">Buka Booking Langsung</a>
                    <a href="{{ route('tablet.check-in') }}" class="text-stone-300 hover:text-[#c9512d] underline">Buka Tablet Kiosk</a>
                </div>
            </div>
        </div>
    </div>

    <!-- LIVE DEMO THEATER & OVERLAY -->
    <div x-show="demoActive" 
         class="fixed inset-0 z-50 overflow-hidden flex flex-col justify-between"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <!-- Dark Backdrop with spotlight cutout -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-[2px] transition-all duration-500 pointer-events-none"></div>

        <!-- Top Live Demo Status Bar -->
        <div class="relative z-50 px-4 py-3 bg-[#171615]/90 border-b border-stone-800 flex items-center justify-between text-white backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-[#c9512d]/20 text-[#c9512d] border border-[#c9512d]/30 text-[10px] font-mono font-bold uppercase tracking-widest">
                    <span class="w-2 h-2 rounded-full bg-[#c9512d] animate-ping"></span>
                    <span>LIVE DEMO AKTIF</span>
                </div>
                <span class="text-xs sm:text-sm font-bold font-headline uppercase" x-text="currentTour.title"></span>
            </div>

            <!-- Auto-play & Close Controls -->
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="toggleAutoPlay()"
                        class="px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-mono font-bold flex items-center gap-1.5 transition">
                    <span x-text="isAutoPlay ? '⏸ Jeda' : '▶ Putar Otomatis'"></span>
                </button>
                <button type="button" 
                        @click="stopDemo()"
                        class="p-1.5 rounded-lg bg-stone-800 hover:bg-red-950 text-stone-400 hover:text-red-400 transition"
                        title="Tutup Demo">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Main Theater Stage: Interactive Live Simulation -->
        <div class="relative z-40 flex-1 flex items-center justify-center p-4 overflow-auto">
            
            <!-- Simulated Screen Container (Laptop / Tablet Frame) -->
            <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl border-4 border-stone-800 overflow-hidden flex flex-col relative transition-all duration-500 transform"
                 style="min-height: 520px; max-height: 80vh;">
                
                <!-- Simulated Window Chrome -->
                <div class="px-4 py-3 bg-[#171615] flex items-center justify-between border-b border-stone-800 text-xs text-stone-400 font-mono select-none">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                        <span class="ml-2 text-stone-300 font-bold" x-text="activeDemoType === 'booking' ? 'defineyoumore.com/booking' : 'morehairstudio.local/tablet/check-in'"></span>
                    </div>
                    <span class="text-[10px] text-[#c9512d] font-bold">Simulasi Interaktif MORE</span>
                </div>

                <!-- Simulation Viewport -->
                <div class="p-6 overflow-y-auto flex-1 bg-stone-50 relative" id="demo-viewport">
                    
                    <!-- ============================================== -->
                    <!-- SCENARIO A: BOOKING WIZARD SIMULATION          -->
                    <!-- ============================================== -->
                    <template x-if="activeDemoType === 'booking'">
                        <div class="space-y-6 max-w-2xl mx-auto">
                            <!-- Stepper pills -->
                            <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                                <span class="text-xs font-mono uppercase font-bold"
                                      :class="currentStep >= 1 ? 'text-[#c9512d]' : 'text-stone-400'">01. Layanan</span>
                                <span class="text-stone-300">&rarr;</span>
                                <span class="text-xs font-mono uppercase font-bold"
                                      :class="currentStep >= 2 ? 'text-[#c9512d]' : 'text-stone-400'">02. Hair Artist</span>
                                <span class="text-stone-300">&rarr;</span>
                                <span class="text-xs font-mono uppercase font-bold"
                                      :class="currentStep >= 3 ? 'text-[#c9512d]' : 'text-stone-400'">03. Jadwal &amp; Jam</span>
                                <span class="text-stone-300">&rarr;</span>
                                <span class="text-xs font-mono uppercase font-bold"
                                      :class="currentStep >= 4 ? 'text-[#c9512d]' : 'text-stone-400'">04. Tiket Selesai</span>
                            </div>

                            <!-- STEP 1: SERVICE -->
                            <div id="sim-step-1" 
                                 class="p-5 rounded-2xl bg-white border-2 transition-all duration-300"
                                 :class="currentStep === 1 ? 'border-[#c9512d] shadow-lg ring-4 ring-[#c9512d]/20' : 'border-stone-200 opacity-60'">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-mono uppercase font-bold text-[#c9512d]">Pilih Treatment</span>
                                    <span class="text-xs font-mono font-bold text-stone-500">45 Menit</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-base text-stone-900">Signature Cut &amp; Scalp Wash</h4>
                                        <p class="text-xs text-stone-500">Konsultasi bentuk wajah, precision cutting, dan hair massage.</p>
                                    </div>
                                    <span class="text-sm font-mono font-black text-[#c9512d]">Rp 150.000</span>
                                </div>
                                <div class="mt-3 text-right">
                                    <span id="sim-btn-service" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition"
                                          :class="currentStep >= 2 ? 'bg-stone-100 text-stone-700 font-mono' : 'bg-[#c9512d] text-white'">
                                        <span x-text="currentStep >= 2 ? '✓ Layanan Terpilih' : 'Pilih Layanan Ini'"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- STEP 2: STYLIST -->
                            <div id="sim-step-2" 
                                 class="p-5 rounded-2xl bg-white border-2 transition-all duration-300"
                                 :class="currentStep === 2 ? 'border-[#c9512d] shadow-lg ring-4 ring-[#c9512d]/20' : 'border-stone-200 opacity-60'">
                                <span class="text-[10px] font-mono uppercase font-bold text-[#c9512d] block mb-2">Pilih Hair Artist</span>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-[#171615] text-[#c9512d] font-bold font-headline text-lg flex items-center justify-center border-2 border-[#c9512d]">
                                        H
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-base text-stone-900">HeyDud</h4>
                                        <p class="text-xs text-stone-500">Creative Senior Hair Artist • Rating ★ 5.0</p>
                                    </div>
                                    <span id="sim-btn-stylist" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition"
                                          :class="currentStep >= 3 ? 'bg-stone-100 text-stone-700 font-mono' : 'bg-[#c9512d] text-white'">
                                        <span x-text="currentStep >= 3 ? 'HeyDud Terpilih' : 'Pilih Stylist'"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- STEP 3: SLOT & TIME -->
                            <div id="sim-step-3" 
                                 class="p-5 rounded-2xl bg-white border-2 transition-all duration-300"
                                 :class="currentStep === 3 ? 'border-[#c9512d] shadow-lg ring-4 ring-[#c9512d]/20' : 'border-stone-200 opacity-60'">
                                <span class="text-[10px] font-mono uppercase font-bold text-[#c9512d] block mb-2">Pilih Tanggal &amp; Jam Sesi</span>
                                <div class="flex gap-2 mb-3">
                                    <span class="px-3 py-1 rounded-lg bg-[#c9512d] text-white text-xs font-mono font-bold">Hari Ini</span>
                                    <span class="px-3 py-1 rounded-lg bg-stone-100 text-stone-600 text-xs font-mono">Besok</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <span class="p-2.5 rounded-xl border border-stone-200 bg-stone-100 text-stone-400 text-xs text-center line-through">11:00</span>
                                    <span id="sim-slot-picked" class="p-2.5 rounded-xl border-2 border-[#c9512d] bg-[#f8eee8] text-[#c9512d] font-mono text-xs font-bold text-center">13:00 (Tersedia)</span>
                                    <span class="p-2.5 rounded-xl border border-stone-200 bg-white text-stone-800 text-xs text-center font-mono">14:00</span>
                                </div>
                                                          <!-- STEP 4: TICKET PASS SUMMARY -->
                            <div id="sim-step-4" 
                                 class="p-5 rounded-2xl bg-white border-2 transition-all duration-300 text-center space-y-2"
                                 :class="currentStep === 4 ? 'border-[#c9512d] shadow-lg ring-4 ring-[#c9512d]/20' : 'border-stone-200 opacity-60'">
                                <span class="text-[10px] font-mono uppercase font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full inline-block">
                                    Booking Berhasil Terkonfirmasi
                                </span>
                                <h4 class="text-xl font-mono font-black text-stone-900 tracking-wider">MORE-{{ date('ymd') }}-ZYULB</h4>
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-[#c9512d]/10 text-[#c9512d] text-xs font-mono font-bold">
                                    <span>Kode Unik Tablet:</span>
                                    <span class="font-black underline tracking-widest text-sm">ZYULB</span>
                                </div>
                                <p class="text-xs text-stone-500">Simpan kode ini atau tunjukkan QR Tiket di tablet studio!</p>
                            </div>
                        </div>
                    </template>

                    <!-- ============================================== -->
                    <!-- SCENARIO B: TABLET CHECK-IN SIMULATION         -->
                    <!-- ============================================== -->
                    <template x-if="activeDemoType === 'checkin'">
                        <div class="space-y-6 max-w-xl mx-auto">
                            
                            <!-- Tablet Top Title -->
                            <div class="text-center space-y-1">
                                <span class="text-[10px] font-mono uppercase font-bold text-[#c9512d] tracking-widest">Kiosk Kedatangan Concierge</span>
                                <h4 class="text-xl font-black font-headline text-stone-900 uppercase">Self Check-In Customer</h4>
                            </div>

                            <!-- Mode Selector Tabs -->
                            <div id="sim-tablet-tabs" class="flex p-1 bg-stone-200 rounded-2xl border border-stone-300">
                                <span class="flex-1 py-2 px-3 text-xs font-bold text-center rounded-xl bg-white text-stone-900 shadow-xs">
                                    Check-In Booking
                                </span>
                                <span class="flex-1 py-2 px-3 text-xs font-bold text-center rounded-xl text-stone-500">
                                    Absen Stylist
                                </span>
                            </div>

                            <!-- Input Box Simulation (Automated MORE-yymmdd- Prefix) -->
                            <div id="sim-tablet-input-box" 
                                 class="p-5 rounded-2xl bg-white border-2 space-y-3 transition-all duration-300"
                                 :class="currentStep >= 2 ? 'border-[#c9512d] shadow-lg ring-4 ring-[#c9512d]/20' : 'border-stone-200'">
                                <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-black">
                                    Cukup Masukkan Kode Unik (Awalan MORE-{{ date('ymd') }}- Otomatis)
                                </label>
                                <div class="flex items-stretch rounded-xl border-2 border-stone-300 overflow-hidden">
                                    <span class="bg-stone-100 text-stone-600 font-mono text-sm font-black px-4 flex items-center border-r border-stone-200 whitespace-nowrap">
                                        MORE-{{ date('ymd') }}-
                                    </span>
                                    <input type="text" 
                                           readonly
                                           :value="currentStep >= 2 ? 'ZYULB' : ''"
                                           placeholder="Ketik 5 karakter unik..." 
                                           class="flex-1 px-4 py-3 text-base font-mono font-black text-[#c9512d] bg-white focus:outline-none tracking-widest">
                                    <span id="sim-btn-proses" class="px-5 bg-[#c9512d] text-white text-xs font-black uppercase flex items-center justify-center">
                                        Proses
                                    </span>
                                </div>
                            </div>    </div>

                            <!-- Result Found -->
                            <div id="sim-tablet-result" 
                                 class="p-5 rounded-2xl bg-emerald-50 border-2 border-emerald-300 space-y-3 transition-all duration-300"
                                 x-show="currentStep >= 3">
                                <div class="flex justify-between items-center border-b border-emerald-200 pb-2">
                                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Data Booking Ditemukan</span>
                                    <span class="text-xs font-mono font-bold text-emerald-700">KODE: ZYULB</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs text-stone-700">
                                    <div>
                                        <span class="text-[10px] text-stone-400 block font-mono">Pelanggan</span>
                                        <span class="font-bold">Raka Pratama</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-stone-400 block font-mono">Hair Artist</span>
                                        <span class="font-bold">HeyDud</span>
                                    </div>
                                </div>
                                <div class="pt-2 text-right">
                                    <span id="sim-btn-checkin-now" class="inline-flex items-center px-4 py-2 bg-[#c9512d] text-white text-xs font-bold rounded-xl shadow-sm">
                                        Check-In Sekarang &rarr;
                                    </span>
                                </div>
                            </div>

                            <!-- Welcome Screen (Step 4) -->
                            <div class="p-6 rounded-2xl bg-[#171615] text-white text-center space-y-2 border border-stone-800"
                                 x-show="currentStep === 4">
                                <div class="w-8 h-8 rounded-full bg-[#c9512d]/20 text-[#c9512d] border border-[#c9512d]/30 flex items-center justify-center mx-auto mb-1 font-serif italic text-xs font-bold">i</div>
                                <h4 class="font-black text-lg text-[#c9512d] font-headline uppercase">Selamat Datang di MORE!</h4>
                                <p class="text-xs text-stone-300">Check-in berhasil. Silakan duduk santai di lounge, Hair Artist HeyDud akan segera melayani Anda.</p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Animated Virtual Cursor -->
                <div id="virtual-cursor"
                     class="absolute z-50 pointer-events-none transition-all duration-700 ease-out"
                     :style="`top: ${cursorY}px; left: ${cursorX}px;`">
                    
                    <!-- Mouse Pointer SVG -->
                    <div class="relative">
                        <svg class="w-7 h-7 text-[#171615] drop-shadow-xl" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4 0l16 12.279-6.951 1.17 4.325 8.817-3.596 1.734-4.35-8.879-5.428 5.702z"/>
                        </svg>
                        
                        <!-- Glowing Orange Dot -->
                        <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-[#c9512d] border-2 border-white shadow-md"></span>

                        <!-- Click ripple animation -->
                        <span x-show="isClicking" 
                              class="absolute -top-3 -left-3 w-9 h-9 rounded-full border-2 border-[#c9512d] animate-ping opacity-75"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Guidance Narration Card (Bottom) -->
        <div class="relative z-50 p-4 max-w-2xl mx-auto w-full">
            <div class="bg-[#171615]/95 border border-[#c9512d]/40 rounded-3xl p-5 shadow-2xl backdrop-blur-md space-y-4 text-white">
                
                <!-- Progress Bar -->
                <div class="w-full bg-stone-800 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-[#c9512d] h-full transition-all duration-300"
                         :style="`width: ${(currentStep / totalSteps) * 100}%`"></div>
                </div>

                <!-- Step Info -->
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 font-mono text-[10px] text-[#c9512d] font-bold uppercase tracking-wider">
                            <span>Langkah <span x-text="currentStep"></span> dari <span x-text="totalSteps"></span></span>
                            <span>•</span>
                            <span x-text="currentStepData.badge"></span>
                        </div>
                        <h4 class="text-base font-bold text-white font-headline" x-text="currentStepData.title"></h4>
                        <p class="text-xs text-stone-300 font-light leading-relaxed" x-text="currentStepData.desc"></p>
                    </div>

                    <!-- Navigation Controls -->
                    <div class="flex items-center gap-2 shrink-0 pt-2">
                        <button type="button"
                                @click="prevStep()"
                                :disabled="currentStep <= 1"
                                class="p-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 disabled:opacity-30 disabled:cursor-not-allowed transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        
                        <button type="button"
                                @click="nextStep()"
                                class="px-4 py-2 rounded-xl bg-[#c9512d] hover:bg-[#b04322] text-white text-xs font-mono font-bold uppercase tracking-wider transition flex items-center gap-1 shadow-md">
                            <span x-text="currentStep >= totalSteps ? 'Selesai' : 'Lanjut'"></span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function moreInteractiveGuide() {
    return {
        openMenu: false,
        demoActive: false,
        activeDemoType: 'booking', // 'booking' | 'checkin'
        currentStep: 1,
        totalSteps: 4,
        isAutoPlay: true,
        isClicking: false,
        timer: null,
        cursorX: 100,
        cursorY: 100,

        tours: {
            booking: {
                title: 'Live Demo: Cara Booking Pengalaman MORE',
                steps: [
                    {
                        step: 1,
                        badge: 'Pilih Treatment',
                        title: '01. Memilih Layanan & Durasi',
                        desc: 'Pilih treatment yang Anda butuhkan (contoh: Signature Cut). Anda dapat melihat rincian harga transparan dan estimasi waktu pengerjaan.',
                        cursorTarget: 'sim-btn-service'
                    },
                    {
                        step: 2,
                        badge: 'Pilih Hair Artist',
                        title: '02. Memilih Hair Stylist Favorit',
                        desc: 'Tentukan Hair Artist yang Anda percaya (contoh: HeyDud). Sistem akan memuat portofolio dan jadwal kerja stylist secara spesifik.',
                        cursorTarget: 'sim-btn-stylist'
                    },
                    {
                        step: 3,
                        badge: 'Pilih Jam Sesi',
                        title: '03. Menentukan Waktu Sesi',
                        desc: 'Gunakan filter tanggal cepat (Hari Ini, Besok, Lusa) lalu pilih jam sesi yang masih tersedia (warna menyala). Jam yang sudah dibooking otomatis terkunci.',
                        cursorTarget: 'sim-slot-picked'
                    },
                    {
                        step: 4,
                        badge: 'E-Ticket Diterbitkan',
                        title: '04. Tiket Digital & Kode Unik',
                        desc: 'Setelah konfirmasi, Anda langsung menerima E-Ticket dan 5 digit Kode Unik (contoh: ZYULB) untuk check-in instan saat tiba di studio!',
                        cursorTarget: 'sim-step-4'
                    }
                ]
            },
            checkin: {
                title: 'Live Demo: Cara Check-In di Tablet Kiosk',
                steps: [
                    {
                        step: 1,
                        badge: 'Kiosk Concierge',
                        title: '01. Tiba di Studio & Dekati Tablet',
                        desc: 'Saat Anda tiba di Jl. Mangga No. 37A Bandung, terdapat tablet kiosk concierge di meja resepsionis untuk verifikasi kedatangan.',
                        cursorTarget: 'sim-tablet-tabs'
                    },
                    {
                        step: 2,
                        badge: 'Input Kode Unik',
                        title: '02. Cukup Ketik 5 Digit Kode Unik',
                        desc: 'Awalan tanggal MORE- sudah terpasang otomatis dan diperbarui setiap hari sesuai jadwal reservasi untuk keamanan! Anda hanya perlu mengetik 5 karakter unik (contoh: ZYULB).',
                        cursorTarget: 'sim-btn-proses'
                    },
                    {
                        step: 3,
                        badge: 'Konfirmasi Kehadiran',
                        title: '03. Verifikasi Data Booking',
                        desc: 'Sistem menampilkan data reservasi dan stylist Anda. Cukup klik tombol "Check-In Sekarang" untuk konfirmasi.',
                        cursorTarget: 'sim-btn-checkin-now'
                    },
                    {
                        step: 4,
                        badge: 'Check-In Sukses',
                        title: '04. Selesai & Selamat Bersantai',
                        desc: 'Notifikasi otomatis terkirim ke Hair Artist dan antrean kasir. Silakan menikmati hospitality lounge MORE sembari menunggu sesi Anda dimulai!',
                        cursorTarget: 'demo-viewport'
                    }
                ]
            }
        },

        get currentTour() {
            return this.tours[this.activeDemoType] || this.tours.booking;
        },

        get currentStepData() {
            const steps = this.currentTour.steps;
            return steps[this.currentStep - 1] || steps[0];
        },

        initGuide() {
            // Check if URL has ?tour=booking or ?tour=checkin
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tour') === 'booking') {
                setTimeout(() => this.startDemo('booking'), 600);
            } else if (urlParams.get('tour') === 'checkin') {
                setTimeout(() => this.startDemo('checkin'), 600);
            }
        },

        startDemo(type) {
            this.openMenu = false;
            this.activeDemoType = type;
            this.currentStep = 1;
            this.totalSteps = this.tours[type].steps.length;
            this.demoActive = true;
            this.isAutoPlay = true;

            this.$nextTick(() => {
                this.moveCursorToTarget();
                this.startTimer();
            });
        },

        stopDemo() {
            this.demoActive = false;
            this.clearTimer();
        },

        nextStep() {
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.moveCursorToTarget();
                if (this.isAutoPlay) {
                    this.startTimer();
                }
            } else {
                this.stopDemo();
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.moveCursorToTarget();
                if (this.isAutoPlay) {
                    this.startTimer();
                }
            }
        },

        toggleAutoPlay() {
            this.isAutoPlay = !this.isAutoPlay;
            if (this.isAutoPlay) {
                this.startTimer();
            } else {
                this.clearTimer();
            }
        },

        startTimer() {
            this.clearTimer();
            this.timer = setTimeout(() => {
                if (this.isAutoPlay && this.demoActive) {
                    this.simulateClick();
                    setTimeout(() => {
                        this.nextStep();
                    }, 800);
                }
            }, 4500);
        },

        clearTimer() {
            if (this.timer) {
                clearTimeout(this.timer);
                this.timer = null;
            }
        },

        moveCursorToTarget() {
            this.$nextTick(() => {
                const targetId = this.currentStepData.cursorTarget;
                const targetEl = document.getElementById(targetId);
                const viewport = document.getElementById('demo-viewport');

                if (targetEl && viewport) {
                    const vRect = viewport.getBoundingClientRect();
                    const tRect = targetEl.getBoundingClientRect();

                    // Relative position inside the simulated window
                    this.cursorX = Math.max(30, tRect.left - vRect.left + (tRect.width / 2));
                    this.cursorY = Math.max(30, tRect.top - vRect.top + (tRect.height / 2) + 40);
                } else {
                    this.cursorX = 250;
                    this.cursorY = 200;
                }
            });
        },

        simulateClick() {
            this.isClicking = true;
            setTimeout(() => {
                this.isClicking = false;
            }, 400);
        }
    };
}
</script>
