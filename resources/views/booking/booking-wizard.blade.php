@extends('layouts.booking')

@section('content')
@php
    $isId = session('locale', 'id') === 'id';
    
    $steps = $isId ? [
        1 => 'Pilih Layanan',
        2 => 'Pilih Stylist',
        3 => 'Pilih Waktu',
        4 => 'Konfirmasi'
    ] : [
        1 => 'Choose Experience',
        2 => 'Meet your barber',
        3 => 'Choose Date & Time',
        4 => 'Confirm'
    ];
@endphp

<div id="booking-wizard-container" 
     class="grid grid-cols-1 lg:grid-cols-3 gap-8 font-sans"
     x-data="bookingWizard()"
     x-init="init()">
     
    <!-- Stepper & Main Column (Left/Center) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Back Navigation Bar -->
        <div class="flex items-center justify-between">
            <button type="button" 
                    @click="handleTopBack()" 
                    class="inline-flex items-center gap-2 text-xs font-semibold text-stone-600 hover:text-[#c9512d] transition duration-200 group">
                <span class="w-8 h-8 rounded-xl bg-white border border-stone-200 flex items-center justify-center group-hover:border-[#c9512d] group-hover:bg-[#faede7] transition shadow-2xs">
                    <svg class="w-4 h-4 text-stone-600 group-hover:text-[#c9512d] group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </span>
                <span x-text="getBackStepLabel()"></span>
            </button>
            <span class="text-xs font-mono font-medium text-stone-400">
                Tahap <span x-text="step" class="font-bold text-stone-700"></span> dari 4
            </span>
        </div>
        
        <!-- Premium Stepper (4 steps) -->
        <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between relative">
                <!-- Connective timeline bar -->
                <div class="absolute left-6 right-6 top-5 h-0.5 bg-stone-100 -z-10"></div>

                @foreach($steps as $num => $label)
                    <div class="flex flex-col items-center flex-1 relative z-10">
                        <!-- Step Badge Circle -->
                        <template x-if="step > {{ $num }} || ({{ $num }} === 2 && preselectedStylistId && selectedStylistId)">
                            <div @click="step = {{ $num }}" class="h-10 w-10 rounded-lg bg-[#1c1917] hover:bg-[#c9512d] text-white flex items-center justify-center font-bold text-sm shadow-sm select-none cursor-pointer transition-colors" title="{{ $isId ? 'Kembali ke langkah '.$num : 'Back to step '.$num }}">
                                ✓
                            </div>
                        </template>
                        <template x-if="step === {{ $num }} && !({{ $num }} === 2 && preselectedStylistId && selectedStylistId)">
                            <div class="h-10 w-10 rounded-lg border-2 border-[#faede7] bg-[#c9512d] text-white flex items-center justify-center font-bold text-sm shadow-sm select-none">
                                0{{ $num }}
                            </div>
                        </template>
                        <template x-if="step < {{ $num }} && !({{ $num }} === 2 && preselectedStylistId && selectedStylistId)">
                            <div class="h-10 w-10 rounded-lg bg-stone-100 text-stone-400 border border-stone-200 flex items-center justify-center font-bold text-sm select-none">
                                0{{ $num }}
                            </div>
                        </template>
                        
                        <!-- Step Label -->
                        <span class="text-[9px] sm:text-[10px] uppercase tracking-wider font-extrabold mt-3 text-center transition-colors"
                              :class="step === {{ $num }} ? 'text-[#c9512d]' : (step > {{ $num }} ? 'text-stone-700 cursor-pointer hover:text-[#c9512d]' : 'text-stone-400')"
                              @click="if (step > {{ $num }}) step = {{ $num }}">
                            {{ $label }}
                        </span>

                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Step Content Card -->
        <div class="bg-white border border-stone-200 rounded-2xl p-8 shadow-sm min-h-[450px]">
            
            <!-- Step 1: Choose Experience -->
            <div x-show="step === 1" x-transition>
                <div class="mb-8 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-stone-900 uppercase tracking-tight">
                        {{ $isId ? 'Bagaimana Anda ingin menikmati More?' : 'How would you like to experience More?' }}
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $isId ? 'Pilih lokasi studio terdekat dan jenis perawatan rambut Anda.' : 'Select your preferred studio location and grooming treatment.' }}
                    </p>
                </div>

                <!-- Hair Artist Direct Booking Banner -->
                <div x-show="selectedStylist && preselectedStylistId" class="mb-6 p-4 sm:p-5 rounded-2xl bg-stone-900 text-white shadow-md border border-stone-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl overflow-hidden bg-stone-800 border-2 border-[#c9512d] flex-shrink-0 relative shadow-inner">
                            <img :src="selectedStylist?.photo || ('https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(selectedStylist?.slug || 'artist'))" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] uppercase font-extrabold tracking-widest text-[#c9512d] bg-[#c9512d]/15 px-2 py-0.5 rounded border border-[#c9512d]/30">Direct Artist Booking</span>
                                <span class="text-[10px] text-amber-400 font-bold" x-text="'★ ' + Number(selectedStylist?.rating || 5).toFixed(1)"></span>
                            </div>
                            <h3 class="text-sm sm:text-base font-extrabold text-white mt-0.5" x-text="selectedStylist?.name"></h3>
                            <p class="text-[11px] text-stone-400 font-normal">
                                <span x-text="selectedStylist?.specialization || 'Hair Artist'"></span> • 
                                <span x-text="selectedOutlet?.name || 'Studio MORE'"></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end border-t sm:border-t-0 pt-2.5 sm:pt-0 border-stone-800">
                        <span class="text-[11px] text-stone-400 hidden lg:inline">Artist otomatis terpilih</span>
                        <button type="button" 
                                @click="changeStylist()" 
                                class="text-xs font-semibold text-stone-300 hover:text-white bg-stone-800 hover:bg-stone-700 px-3 py-1.5 rounded-xl border border-stone-700 transition">
                            Ganti Artist
                        </button>
                    </div>
                </div>

                <!-- 1A: Outlet Selector -->
                <div class="space-y-6">
                    <div x-show="!isWalkIn">
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-4">
                            {{ $isId ? '01. Pilih Lokasi Studio' : '01. Select Location' }}
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <template x-for="outlet in outlets" :key="outlet.id">
                                <div class="border rounded-2xl overflow-hidden cursor-pointer transition flex flex-col justify-between"
                                     :class="selectedOutletId == outlet.id ? 'border-[#c9512d] bg-[#faede7]/30 ring-1 ring-[#c9512d]/30 shadow-sm' : 'border-stone-200 hover:border-[#c9512d]/50'"
                                     @click="selectOutlet(outlet.id)">
                                    <!-- Outlet image placeholder/generic -->
                                    <div class="h-40 w-full overflow-hidden bg-stone-50">
                                        <img :src="'/images/outlet_' + outlet.id + '.jpg'" onerror="this.src='https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=500'" :alt="outlet.name" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-5 flex-grow">
                                        <h4 class="font-bold text-stone-800 text-xs uppercase tracking-wider" x-text="outlet.name"></h4>
                                        <span class="text-[10px] text-stone-400 block mt-1 leading-relaxed font-light">Address: <span x-text="outlet.address"></span></span>
                                    </div>
                                    <div x-show="selectedOutletId == outlet.id" class="bg-[#c9512d] text-white text-[9px] uppercase font-extrabold tracking-widest text-center py-2">
                                        {{ $isId ? 'Studio Terpilih' : 'Selected Location' }}
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 1B: Service Selector -->
                    <div x-show="selectedOutletId" :class="isWalkIn ? '' : 'pt-6 border-t border-stone-100'">
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-4">
                            <span x-text="isWalkIn ? '{{ $isId ? 'Pilih Gaya Rambut Anda' : 'Find your style' }}' : '{{ $isId ? '02. Pilih Gaya Rambut Anda' : '02. Find your style' }}'"></span>
                        </label>
                        
                        <div class="space-y-8" x-data="{ openCat: 0 }">
                            <template x-for="category in categories" :key="category.id">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center border-b border-stone-150 pb-2 cursor-pointer"
                                         @click="openCat = (openCat === category.id ? 0 : category.id)">
                                        <h4 class="text-[10px] font-extrabold uppercase tracking-widest text-stone-450" x-text="category.name"></h4>
                                        <span class="text-stone-400 text-[10px]" x-text="openCat === category.id ? '▲' : '▼'"></span>
                                    </div>
                                    
                                    <div class="space-y-4" x-show="openCat === 0 || openCat === category.id">
                                        <template x-for="service in getServicesByCategory(category.id)" :key="service.id">
                                            <div class="border border-stone-200 rounded-xl p-5 bg-white hover:border-[#c9512d] hover:shadow-sm transition cursor-pointer flex justify-between items-center group"
                                                 @click="selectService(service.id)">
                                                <div class="flex-grow pr-4">
                                                    <h5 class="font-bold text-stone-900 text-xs uppercase tracking-tight group-hover:text-[#c9512d] transition-colors" x-text="service.name"></h5>
                                                    <div class="text-[10px] text-stone-500 mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                                                        <span>{{ $isId ? 'Durasi' : 'Duration' }}: <span x-text="getServiceDuration(service)" class="font-bold text-stone-700"></span> Min</span>
                                                        <span>•</span>
                                                        <button type="button" 
                                                                @click.stop="openServiceModal(service)" 
                                                                class="text-[#c9512d] hover:text-[#963b1d] font-bold underline underline-offset-2 transition"
                                                                title="{{ $isId ? 'Buka informasi detail layanan ini' : 'View full service details' }}">
                                                            {{ $isId ? 'Lihat Detail' : 'Details' }}
                                                        </button>
                                                        <span>•</span>
                                                        <span class="font-bold text-stone-900">Rp <span x-text="formatNumber(getServicePrice(service))"></span></span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <button type="button" 
                                                            @click.stop="openServiceModal(service)" 
                                                            class="hidden sm:inline-flex items-center text-[10px] font-bold uppercase tracking-wider text-stone-500 hover:text-[#c9512d] bg-stone-50 hover:bg-[#faede7] px-3 py-1.5 rounded-lg border border-stone-200 transition">
                                                        {{ $isId ? 'Detail' : 'Details' }}
                                                    </button>
                                                    <span class="text-stone-300 group-hover:text-[#c9512d] group-hover:translate-x-0.5 text-xs transition">&#10095;</span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Meet Your Barber -->
            <div x-show="step === 2" x-transition>
                <div class="mb-8 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-stone-900 uppercase tracking-tight">
                        {{ $isId ? 'Pilih Stylist Anda' : 'Meet your barber' }}
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $isId ? 'Pilih stylist ahli kami untuk menangani Anda.' : 'Select our expert stylist to design your style.' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="stylist in getStylistsByOutlet()" :key="stylist.id">
                        <div class="border border-stone-200 rounded-2xl p-5 bg-white hover:border-[#c9512d] hover:shadow-md transition cursor-pointer flex items-center space-x-4 group"
                             @click="selectStylist(stylist.id)">
                            <div class="h-16 w-16 rounded-xl overflow-hidden border border-stone-200 shadow-sm flex-shrink-0 bg-stone-100 flex items-center justify-center relative">
                                <img :src="stylist.photo || ('https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(stylist.slug))" :alt="stylist.name" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200">
                                <span class="absolute bottom-1 right-1 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                            </div>
                            <div class="flex-grow min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="font-bold text-stone-900 text-sm uppercase tracking-tight group-hover:text-[#c9512d] transition-colors truncate" x-text="stylist.name"></h4>
                                    <a :href="'/' + stylist.slug" target="_blank" @click.stop class="text-[10px] text-stone-400 hover:text-[#c9512d] font-semibold underline underline-offset-2 flex-shrink-0">Profil &rarr;</a>
                                </div>
                                <span class="text-[9px] text-[#c9512d] uppercase font-extrabold tracking-wider block mt-0.5" x-text="stylist.specialization"></span>
                                <p class="text-stone-500 text-[11px] leading-relaxed mt-1.5 line-clamp-2" x-text="stylist.bio || 'Mendedikasikan keahlian presisi dan pemahaman tekstur rambut alami.'">
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-8 pt-4 border-t border-stone-100 flex justify-between items-center">
                    <button type="button" @click="prevStep()" class="px-4 py-2 border border-stone-200 rounded-xl text-stone-600 hover:bg-stone-50 font-bold text-xs flex items-center gap-2 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>{{ $isId ? 'Kembali ke Pilih Layanan' : 'Back to Choose Experience' }}</span>
                    </button>
                </div>
            </div>


            <!-- Step 3: Choose Date & Time -->
            <div x-show="step === 3" x-transition>
                <div class="mb-8 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-stone-900 uppercase tracking-tight">
                        <span x-text="isWalkIn ? '{{ $isId ? 'Waktu Sesi Walk-In (Hari Ini)' : 'Walk-In Session Time (Today)' }}' : '{{ $isId ? 'Pilih Tanggal & Jam Sesi' : 'Choose Date & Time' }}'"></span>
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        <span x-text="isWalkIn ? '{{ $isId ? 'Layanan Walk-In dijadwalkan langsung hari ini (' . \Carbon\Carbon::now()->translatedFormat('l, d F Y') . ') pada waktu saat ini.' : 'Walk-In service is scheduled directly for right now today.' }}' : '{{ $isId ? 'Tentukan tanggal kunjungan dan jam sesi Anda.' : 'Select your visit date and preferred session slot.' }}'"></span>
                    </p>
                </div>

                <div :class="isWalkIn ? 'space-y-6' : 'grid grid-cols-1 lg:grid-cols-3 gap-8'">
                    <!-- Left Column: Interactive Mini-Calendar & Date Selector (HIDDEN FOR WALKIN) -->
                    <div class="lg:col-span-1 space-y-4" x-show="!isWalkIn">
                        <div class="border border-stone-200 rounded-2xl p-5 bg-white shadow-2xs space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-[10px] uppercase tracking-widest text-[#c9512d] font-extrabold">
                                    {{ $isId ? '01. PILIH TANGGAL' : '01. SELECT DATE' }}
                                </label>
                                <button type="button" @click="setWizardDate('{{ \Carbon\Carbon::today()->toDateString() }}')" class="text-[10px] font-bold text-[#c9512d] hover:underline">
                                    {{ $isId ? 'Hari Ini' : 'Today' }}
                                </button>
                            </div>

                            <!-- Calendar Header (Month + Year + Nav) -->
                            <div class="flex items-center justify-between border-b border-stone-100 pb-2.5">
                                <button type="button" 
                                        @click="prevCalMonth()" 
                                        class="p-1 rounded-lg border border-stone-200 text-stone-600 hover:bg-stone-50 hover:text-stone-900 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>

                                <span class="text-xs font-black uppercase tracking-tight text-stone-900 font-mono" x-text="calMonthLabel"></span>

                                <button type="button" 
                                        @click="nextCalMonth()" 
                                        class="p-1 rounded-lg border border-stone-200 text-stone-600 hover:bg-stone-50 hover:text-stone-900 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>

                            <!-- Day names -->
                            <div class="grid grid-cols-7 gap-1 text-center">
                                <template x-for="dayName in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="dayName">
                                    <div class="text-[9px] font-bold text-stone-400 uppercase py-0.5" x-text="dayName"></div>
                                </template>
                            </div>

                            <!-- Calendar Days Grid -->
                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="blank in calDays.blanks" :key="'blank-' + blank">
                                    <div class="h-7"></div>
                                </template>

                                <template x-for="day in calDays.days" :key="day.dateStr">
                                    <button type="button"
                                            :disabled="day.isPast"
                                            @click="setWizardDate(day.dateStr)"
                                            class="h-7 rounded-md flex items-center justify-center text-[11px] font-mono transition"
                                            :class="{
                                                'bg-[#c9512d] text-white font-black shadow-xs ring-1 ring-[#c9512d]': selectedDate === day.dateStr,
                                                'text-stone-300 cursor-not-allowed': day.isPast,
                                                'text-stone-700 hover:bg-[#faede7] hover:text-[#c9512d] cursor-pointer font-bold': !day.isPast && selectedDate !== day.dateStr,
                                                'ring-1 ring-stone-300': day.isToday && selectedDate !== day.dateStr
                                            }">
                                        <span x-text="day.dayNumber"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Quick Date Buttons -->
                            <div class="pt-3 border-t border-stone-100 flex flex-wrap gap-1.5">
                                <button type="button" 
                                        @click="setWizardDate('{{ \Carbon\Carbon::today()->toDateString() }}')"
                                        class="py-1 px-2 rounded-md text-[10px] font-mono font-bold transition"
                                        :class="selectedDate === '{{ \Carbon\Carbon::today()->toDateString() }}' ? 'bg-[#c9512d] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'">
                                    Hari Ini
                                </button>
                                <button type="button" 
                                        @click="setWizardDate('{{ \Carbon\Carbon::tomorrow()->toDateString() }}')"
                                        class="py-1 px-2 rounded-md text-[10px] font-mono font-bold transition"
                                        :class="selectedDate === '{{ \Carbon\Carbon::tomorrow()->toDateString() }}' ? 'bg-[#c9512d] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'">
                                    Besok
                                </button>
                                <button type="button" 
                                        @click="setWizardDate('{{ \Carbon\Carbon::today()->addDays(2)->toDateString() }}')"
                                        class="py-1 px-2 rounded-md text-[10px] font-mono font-bold transition"
                                        :class="selectedDate === '{{ \Carbon\Carbon::today()->addDays(2)->toDateString() }}' ? 'bg-[#c9512d] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'">
                                    Lusa
                                </button>
                            </div>

                            <!-- Native date input fallback -->
                            <div class="pt-2">
                                <input type="date" 
                                       class="w-full px-3 py-2 bg-[#fafaf9] border border-stone-200 rounded-lg text-xs font-mono text-stone-900 focus:outline-none focus:border-[#c9512d] transition"
                                       x-model="selectedDate"
                                       @change="setWizardDate($event.target.value)"
                                       min="{{ \Carbon\Carbon::today()->toDateString() }}" />
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Slots Availability & Occupied Status -->
                    <div :class="isWalkIn ? 'w-full space-y-6' : 'lg:col-span-2 space-y-6'">
                        <!-- Walk-In Live Session Highlight Card -->
                        <div x-show="isWalkIn" class="bg-[#faede7] border border-[#c9512d]/30 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-[#c9512d] text-white flex items-center justify-center font-bold text-lg flex-shrink-0 shadow-xs">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-[#c9512d] animate-pulse"></span>
                                        <span class="text-xs font-black uppercase tracking-wider text-[#c9512d]">
                                            Sesi Walk-In Langsung (Hari Ini)
                                        </span>
                                    </div>
                                    <span class="text-xs text-stone-700 font-medium block mt-0.5">
                                        Hari ini, {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} &bull; Waktu Otomatis: <strong class="font-mono text-stone-900 font-bold" x-text="manualTime"></strong> WIB
                                    </span>
                                </div>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider bg-[#c9512d] text-white px-3 py-1.5 rounded-full shadow-2xs self-start sm:self-center">
                                Tanggal &amp; Jam Otomatis
                            </span>
                        </div>

                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2" x-show="!isWalkIn">
                            {{ $isId ? 'JADWAL SESI & KETERSEDIAAN STYLIST PADA TANGGAL INI' : 'SESSION SCHEDULE & STYLIST AVAILABILITY FOR THIS DATE' }}
                        </label>
                        
                        <div x-show="loadingSlots" class="text-xs font-bold text-[#c9512d] py-4">
                            Memuat ketersediaan slot...
                        </div>

                        <!-- Preferred Stylist Card -->
                        <div x-show="!loadingSlots && selectedStylist" class="border rounded-2xl p-6 bg-white border-[#c9512d] ring-1 ring-[#c9512d]/20 shadow-sm flex flex-col gap-6">
                            <!-- Top Part: Stylist Profile with Photo -->
                            <div class="flex items-center space-x-4">
                                <div class="h-14 w-14 rounded-2xl overflow-hidden border border-stone-200 shadow-sm flex-shrink-0 bg-stone-100 flex items-center justify-center relative">
                                    <img :src="selectedStylist?.photo || ('https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(selectedStylist ? selectedStylist.slug : ''))" class="h-full w-full object-cover">
                                    <span class="absolute bottom-1 right-1 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h4 class="font-bold text-stone-900 text-sm uppercase tracking-tight" x-text="selectedStylist ? selectedStylist.name : ''"></h4>
                                        <span class="text-[8px] uppercase tracking-wider bg-[#faede7] text-[#c9512d] px-2 py-0.5 rounded font-extrabold">{{ $isId ? 'Pilihan Utama' : 'Preferred' }}</span>
                                    </div>
                                    <span class="text-[10px] text-stone-400 uppercase font-extrabold tracking-wider block mt-0.5" x-text="selectedStylist ? selectedStylist.specialization : ''"></span>
                                </div>
                            </div>

                            <!-- WALKIN ONLY: Live Studio Radar & Service Status (Zero Forms!) -->
                            <div x-show="isWalkIn" class="space-y-6 pt-4 border-t border-stone-100">
                                <div class="p-5 sm:p-6 rounded-2xl border transition-all"
                                     :class="isStylistBusyNow(selectedStylistId) ? 'bg-amber-50/70 border-amber-200' : 'bg-emerald-50/70 border-emerald-200'">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                                        <div class="flex items-start sm:items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-xs"
                                                 :class="isStylistBusyNow(selectedStylistId) ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white'">
                                                <template x-if="isStylistBusyNow(selectedStylistId)">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </template>
                                                <template x-if="!isStylistBusyNow(selectedStylistId)">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0L12 12zm0 0L9.121 9.121a3 3 0 10-4.242 4.242 3 3 0 004.242 0L12 12z"/>
                                                    </svg>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full"
                                                          :class="isStylistBusyNow(selectedStylistId) ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"></span>
                                                    <h4 class="font-black text-sm uppercase tracking-wider"
                                                        :class="isStylistBusyNow(selectedStylistId) ? 'text-amber-900' : 'text-emerald-900'"
                                                        x-text="isStylistBusyNow(selectedStylistId) ? 'Sedang Melayani Customer' : 'Kursi Siap • Siap Melayani Langsung'"></h4>
                                                </div>
                                                <p class="text-xs mt-1 leading-relaxed"
                                                   :class="isStylistBusyNow(selectedStylistId) ? 'text-amber-800' : 'text-emerald-800'"
                                                   x-text="isStylistBusyNow(selectedStylistId) ? ('Stylist sedang menangani customer untuk layanan ' + (getStylistActiveService(selectedStylistId) || '') + '. Estimasi selesai jam ' + (getStylistFinishTime(selectedStylistId) || '') + ' WIB. Anda masuk antrean berikutnya.') : ('Tidak ada antrean customer saat ini. Stylist ' + (selectedStylist?.name || '') + ' dapat langsung memulai pengerjaan rambut Anda!')"></p>
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="confirmTimeAndProceed()" 
                                                class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-[#c9512d] hover:bg-[#b74423] text-white font-bold text-xs uppercase tracking-wider shadow-sm transition flex items-center justify-center gap-2 flex-shrink-0 cursor-pointer">
                                            <span>Mulai Sesi Walk-In</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Live Status of All Stylists in Studio -->
                                <div class="border border-stone-200 rounded-2xl p-5 bg-white space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-stone-400 block">
                                            Status Stylist Lain di Studio Ini (Real-Time)
                                        </span>
                                        <span class="text-[10px] text-stone-400">Klik untuk beralih</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <template x-for="st in getStylistsByOutlet()" :key="st.id">
                                            <div class="p-3.5 rounded-xl border flex items-center justify-between gap-3 cursor-pointer transition"
                                                 :class="selectedStylistId == st.id ? 'border-[#c9512d] bg-[#faede7]/30 ring-1 ring-[#c9512d]/30' : 'border-stone-200 hover:border-stone-300 bg-white'"
                                                 @click="selectStylist(st.id)">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <img :src="st.photo || ('https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(st.slug))" class="w-9 h-9 rounded-xl object-cover border border-stone-200 flex-shrink-0">
                                                    <div class="truncate">
                                                        <span class="font-bold text-xs text-stone-900 block truncate" x-text="st.name"></span>
                                                        <span class="text-[10px] font-bold block mt-0.5"
                                                              :class="isStylistBusyNow(st.id) ? 'text-amber-600' : 'text-emerald-600'"
                                                              x-text="isStylistBusyNow(st.id) ? 'Sedang Melayani' : 'Siap Melayani'"></span>
                                                    </div>
                                                </div>
                                                <span x-show="selectedStylistId == st.id" class="text-xs font-bold text-[#c9512d]">✓</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- REGULAR BOOKING ONLY: Manual Time & Quick Recommendations -->
                            <div x-show="!isWalkIn" class="pt-4 border-t border-stone-100 space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-[10px] uppercase tracking-widest text-stone-900 font-extrabold">
                                            <span x-text="isWalkIn ? '{{ $isId ? 'JAM SESI WALK-IN (OTOMATIS WAKTU SAAT INI)' : 'WALK-IN SESSION TIME (AUTOMATIC NOW)' }}' : '{{ $isId ? 'PILIH JAM SESI (BEBAS / MANUAL)' : 'CHOOSE SESSION TIME (FLEXIBLE / MANUAL)' }}'"></span>
                                        </label>
                                        <span class="text-[10px] text-stone-400 font-medium">
                                            <span x-text="isWalkIn ? '{{ $isId ? 'Otomatis terisi waktu saat ini' : 'Auto-filled with current time' }}' : '{{ $isId ? 'Bebas pilih jam dan menit kedatangan' : 'Pick any hour & minute you prefer' }}'"></span>
                                        </span>
                                    </div>

                                    <!-- Time input row -->
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                        <div class="relative flex-1 max-w-xs">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <input type="time" 
                                                   x-model="manualTime" 
                                                   @input="onManualTimeChange()"
                                                   @change="onManualTimeChange()"
                                                   class="w-full pl-10 pr-4 py-2.5 bg-[#fafaf9] border rounded-xl text-sm font-mono font-bold transition focus:outline-none"
                                                   :class="isTimeValid ? 'border-stone-300 focus:border-[#c9512d] text-stone-900' : 'border-rose-300 text-rose-800 bg-rose-50/30'"
                                                   step="60" />
                                        </div>

                                        <button type="button" 
                                                :disabled="!isTimeValid || !manualTime"
                                                @click="confirmTimeAndProceed()"
                                                class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-2xs transition flex items-center justify-center gap-2 cursor-pointer"
                                                :class="(isTimeValid && manualTime) ? 'bg-[#c9512d] hover:bg-[#a03b1e]' : 'bg-stone-300 cursor-not-allowed opacity-70'">
                                            <span x-text="isWalkIn ? '{{ $isId ? 'Konfirmasi Waktu & Lanjut' : 'Confirm Time & Continue' }}' : '{{ $isId ? 'Pilih Jam Ini & Lanjut' : 'Select Time & Continue' }}'"></span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </button>
                                    </div>

                                    <!-- Clean Minimalist Session Interval & Validation line -->
                                    <div class="mt-2.5 text-xs">
                                        <template x-if="isTimeValid && manualTime">
                                            <div class="text-stone-500 font-mono text-[11px] flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Rentang Sesi: <strong class="text-stone-900 font-bold" x-text="manualTime + ' - ' + calculateEndTime(manualTime) + ' WIB'"></strong> (<span x-text="serviceDuration"></span> Menit) &bull; <span class="text-emerald-600 font-bold">Tersedia</span></span>
                                            </div>
                                        </template>
                                        <template x-if="!isTimeValid">
                                            <div class="text-rose-600 font-medium text-xs flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span x-text="timeConflictMessage"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Compact Quick Recommendations (Hidden for Walk-In) -->
                                <div class="pt-2" x-show="!isWalkIn">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-[9px] uppercase tracking-widest text-stone-400 font-extrabold">
                                            {{ $isId ? 'Rekomendasi Jam (Pilihan Cepat)' : 'Quick Time Recommendations' }}
                                        </label>
                                        <span class="text-[9px] text-stone-400 font-normal">Klik untuk langsung mengisi jam</span>
                                    </div>

                                    <template x-if="getSelectedStylistSlots().length === 0">
                                        <div class="text-[11px] text-stone-400 italic py-1">
                                            {{ $isId ? 'Semua slot otomatis pada tanggal ini penuh. Anda tetap bisa memasukkan jam secara bebas di atas atau memilih tanggal lain.' : 'All automated slots on this date are booked. You can still input manual time or pick another date.' }}
                                        </div>
                                    </template>

                                    <template x-if="getSelectedStylistSlots().length > 0">
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="slot in getSelectedStylistSlots()" :key="slot.time">
                                                <button type="button" 
                                                        class="py-1 px-2.5 rounded-md border text-[11px] font-bold font-mono transition select-none"
                                                        :class="manualTime === slot.time ? 'border-[#c9512d] bg-[#faede7] text-[#c9512d] ring-1 ring-[#c9512d]' : 'border-stone-200 bg-stone-50/70 text-stone-600 hover:border-[#c9512d] hover:bg-white hover:text-[#c9512d]'"
                                                        @click="pickRecommendedSlot(slot.time)">
                                                    <span x-text="slot.time"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Alternative Stylist Suggestion (only shown if preferred stylist is sold out) -->
                        <div x-show="!loadingSlots && getSelectedStylistSlots().length === 0" class="pt-6 border-t border-stone-200 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-tight text-stone-900">
                                    {{ $isId ? 'Rekomendasi Alternatif Stylist Lain' : 'Recommended Alternative Stylists' }}
                                </span>
                                <span class="text-[10px] text-stone-400">
                                    {{ $isId ? 'Pilih salah satu untuk mengganti stylist' : 'Click any to switch stylist' }}
                                </span>
                            </div>
                            
                            <div class="space-y-4">
                                <template x-for="altStylist in getAlternativeStylists()" :key="altStylist.id">
                                    <div class="border border-stone-200 rounded-2xl p-5 bg-white hover:border-[#c9512d] hover:shadow-md transition-all cursor-pointer flex flex-col gap-4 group"
                                         @click="selectStylist(altStylist.id)"
                                         title="{{ $isId ? 'Klik untuk memilih stylist ini' : 'Click to select this stylist' }}">
                                        
                                        <!-- Stylist Header & Select Button -->
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center space-x-3.5">
                                                <div class="h-12 w-12 rounded-lg overflow-hidden border border-stone-200 shadow-inner flex-shrink-0 bg-stone-50 flex items-center justify-center">
                                                    <img :src="'https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(altStylist.slug)" :alt="altStylist.name" class="h-full w-full object-cover">
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-stone-900 text-xs uppercase tracking-tight group-hover:text-[#c9512d] transition-colors" x-text="altStylist.name"></h4>
                                                    <span class="text-[9px] text-stone-400 uppercase font-extrabold tracking-wider block mt-0.5" x-text="altStylist.specialization"></span>
                                                </div>
                                            </div>

                                            <button type="button" 
                                                    @click.stop="selectStylist(altStylist.id)"
                                                    class="px-4 py-2 rounded-xl border border-stone-200 bg-stone-50 group-hover:bg-[#c9512d] group-hover:border-[#c9512d] group-hover:text-white text-xs font-bold text-stone-700 transition flex items-center gap-1.5 shadow-2xs">
                                                <span>{{ $isId ? 'Pilih Stylist Ini' : 'Select Stylist' }}</span>
                                                <span class="text-[10px]">&#10095;</span>
                                            </button>
                                        </div>

                                        <!-- Available Slots if any -->
                                        <div x-show="slots[altStylist.id] && slots[altStylist.id].length > 0" class="pt-3 border-t border-stone-100">
                                            <label class="block text-[9px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">
                                                {{ $isId ? 'Sesi Jam Tersedia (Klik Jam Langsung)' : 'Available Session Slots' }}
                                            </label>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="slot in slots[altStylist.id]" :key="slot.time">
                                                    <button type="button" 
                                                            class="py-2 px-3.5 min-w-[70px] rounded-lg border bg-white text-xs font-bold font-mono transition text-center border-stone-200 text-stone-700 hover:border-[#c9512d] hover:bg-[#faede7] hover:text-[#c9512d]"
                                                            @click.stop="selectStylistAndSlot(altStylist.id, slot.time)">
                                                        <span x-text="slot.time"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Fallback notice when no slots on current date -->
                                        <div x-show="!slots[altStylist.id] || slots[altStylist.id].length === 0" class="pt-3 border-t border-stone-100 flex items-center justify-between text-stone-400">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] uppercase tracking-wider font-extrabold bg-stone-100 text-stone-500">
                                                {{ $isId ? 'Jadwal Hari Ini Penuh' : 'Fully Booked for Date' }}
                                            </span>
                                            <span class="text-[10px] text-[#c9512d] font-semibold group-hover:underline">
                                                {{ $isId ? 'Klik kartu untuk ganti ke stylist ini' : 'Click card to switch to this stylist' }} &rarr;
                                            </span>
                                        </div>

                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="mt-8 pt-4 border-t border-stone-100 flex justify-between items-center">
                    <button type="button" @click="prevStep()" class="px-4 py-2 border border-stone-200 rounded-xl text-stone-600 hover:bg-stone-50 font-bold text-xs flex items-center gap-2 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>{{ $isId ? 'Kembali ke Pilih Stylist' : 'Back to Meet Barber' }}</span>
                    </button>

                    <button type="button" 
                            x-show="isWalkIn"
                            :disabled="!isTimeValid || !manualTime"
                            @click="confirmTimeAndProceed()"
                            class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-[#c9512d] hover:bg-[#b74423] shadow-2xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>{{ $isId ? 'Lanjut ke Data Pemesan' : 'Proceed to Customer Details' }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- Step 4: Confirm -->
            <div x-show="step === 4" x-transition>
                <div class="mb-8 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-stone-900 uppercase tracking-tight">
                        {{ $isId ? 'Pesan Pengalaman Anda' : 'Book your experience' }}
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $isId ? 'Lengkapi data diri dan konfirmasi janji temu Anda.' : 'Provide details to secure your customized grooming session.' }}
                    </p>
                </div>

                <div class="space-y-6">
                    <!-- Customer Details Form -->
                    <div class="border border-stone-200 rounded-xl p-6 bg-white space-y-4">
                        <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-stone-400 border-b pb-2">01. Data Diri</h3>
                        
                        <div x-show="autoFillSuccess" class="mb-4">
                            <x-ui.alert variant="info" title="Profil Ditemukan">
                                <span x-text="autoFillSuccess"></span>
                            </x-ui.alert>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input label="Nomor WhatsApp" placeholder="e.g. 081234567890" x-model.debounce.300ms="phone" />
                            <x-ui.input label="{{ $isId ? 'Nama Lengkap' : 'Full Name' }}" placeholder="John Doe" x-model="customerName" />
                        </div>

                        <!-- Customer extra fields: Email, Birth Date, Gender (Complete for both regular and walk-in) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                            <x-ui.input label="Email" type="email" placeholder="john@example.com" x-model="email" />
                            <x-ui.input label="{{ $isId ? 'Tanggal Lahir' : 'Birth Date' }}" type="date" x-model="birthDate" />
                            
                            <div>
                                <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Gender</label>
                                <select x-model="gender" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 rounded-xl text-xs text-stone-900 focus:outline-none focus:border-[#c9512d] transition">
                                    <option value="">Pilih Gender</option>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Walk-In Instant Check-In Info Note -->
                        <div x-show="isWalkIn" class="p-4 rounded-xl bg-stone-50 border border-stone-200 text-stone-600 text-xs flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#faede7] text-[#c9512d] flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="text-stone-900 font-bold block">Sesi Walk-In Langsung</strong>
                                <span>Tiket sesi langsung aktif untuk diproses kasir dan pengerjaan oleh stylist hari ini.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Code / Voucher -->
                    <div class="border border-stone-200 rounded-xl p-6 bg-white space-y-4">
                        <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-stone-400 border-b pb-2">02. Kode Promo / Voucher</h3>
                        <div class="flex space-x-3 items-end">
                            <div class="flex-grow">
                                <x-ui.input placeholder="e.g. WELCOME50" x-model="promoCode" />
                            </div>
                            <x-ui.button variant="outline" type="button" @click="applyPromo" class="h-[46px] rounded-lg">Gunakan</x-ui.button>
                        </div>
                        <div x-show="promoError" class="mt-2"><x-ui.alert variant="danger"><span x-text="promoError"></span></x-ui.alert></div>
                        <div x-show="promoSuccess" class="mt-2"><x-ui.alert variant="success"><span x-text="promoSuccess"></span></x-ui.alert></div>
                    </div>

                    @php
                        $isGatewayActive = \App\Domains\CMS\Services\CmsService::get('payment_gateway_active') === 'true';
                    @endphp

                    @if($isGatewayActive)
                        <!-- Payment Selector -->
                        <div class="border border-stone-200 rounded-xl p-6 bg-white space-y-4">
                            <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-stone-400 border-b pb-2">03. Metode Pembayaran</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border rounded-lg p-4 cursor-pointer text-center"
                                     :class="paymentMethod === 'manual' ? 'border-[#c9512d] bg-[#faede7]/30 text-[#c9512d]' : 'border-stone-200'"
                                     @click="paymentMethod = 'manual'">
                                    <span class="text-xs font-bold block text-stone-850 uppercase">Bayar di Outlet</span>
                                </div>
                                <div class="border rounded-lg p-4 cursor-pointer text-center"
                                     :class="paymentMethod === 'midtrans' ? 'border-[#c9512d] bg-[#faede7]/30 text-[#c9512d]' : 'border-stone-200'"
                                     @click="paymentMethod = 'midtrans'">
                                    <span class="text-xs font-bold block text-stone-850 uppercase">Bayar Sekarang (Online)</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="pt-4 flex justify-between items-center">
                        <button type="button" @click="prevStep" class="px-4 py-2 border border-stone-200 rounded-xl text-stone-600 hover:bg-stone-50 font-bold text-xs">Kembali</button>
                        <button type="button" 
                                @click="confirmBooking"
                                class="px-8 py-3.5 rounded-xl bg-[#c9512d] hover:bg-[#b74423] text-white font-bold text-xs uppercase tracking-wider shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                            <span x-text="isWalkIn ? 'Mulai Treatment Walk-In Sekarang' : 'Book your experience'"></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Summary Cards (Right Column) -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Selected Outlet Details Card -->
        <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm text-center">
            <div class="mb-4">
                <img src="/logo/logo.png" alt="MORE" class="h-10 mx-auto object-contain">
            </div>
            
            <h3 class="text-base font-bold font-sans text-stone-900 mt-2 uppercase tracking-wider" x-text="selectedOutlet ? selectedOutlet.name : 'More Hair Studio'"></h3>

            <p class="text-stone-550 text-xxs leading-relaxed mt-4 border-t border-stone-100 pt-4 px-2 font-light" x-text="selectedOutlet ? selectedOutlet.address : 'Pilih outlet terdekat untuk memuat informasi alamat lengkap.'"></p>
        </div>

        <!-- Ringkasan Pemesanan Card -->
        <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h4 class="font-extrabold text-[10px] uppercase tracking-wider text-stone-400 border-b border-stone-100 pb-2">
                {{ $isId ? 'Ringkasan Pemesanan' : 'Booking Summary' }}
            </h4>

            <div class="text-xs space-y-2.5 font-medium text-stone-600">
                <div class="flex justify-between">
                    <span>Selected Services:</span>
                    <span class="text-stone-850 font-bold font-mono" x-text="selectedServiceId ? 1 : 0"></span>
                </div>
                <div class="flex justify-between">
                    <span>Est. Duration:</span>
                    <span class="text-stone-850 font-bold font-mono">
                        <span x-text="serviceDuration"></span> {{ $isId ? 'Menit' : 'Min' }}
                    </span>
                </div>
                <div class="flex justify-between" x-show="selectedStylist">
                    <span>Stylist:</span>
                    <span class="text-stone-850 font-bold uppercase" x-text="selectedStylist ? selectedStylist.name : ''"></span>
                </div>
                <div class="flex justify-between" x-show="selectedDate">
                    <span>Tanggal:</span>
                    <span class="text-stone-850 font-bold font-mono">
                        <span x-text="isWalkIn ? 'Hari Ini (' + selectedDate + ')' : selectedDate"></span>
                    </span>
                </div>
                <div class="flex justify-between" x-show="selectedTime">
                    <span>Waktu Sesi:</span>
                    <span class="text-stone-850 font-bold font-mono">
                        <span x-text="selectedTime"></span> - <span x-text="calculateEndTime(selectedTime)"></span> WIB
                        <span x-show="isWalkIn" class="text-[9px] text-[#c9512d] font-sans font-bold uppercase ml-1">(Walk-In)</span>
                    </span>
                </div>
                <div class="flex justify-between border-t border-stone-100 pt-2 text-stone-450" x-show="discountAmount > 0">
                    <span>{{ $isId ? 'Harga Asli' : 'Original Price' }}:</span>
                    <span class="font-mono text-stone-700 line-through">
                        Rp <span x-text="formatNumber(servicePrice)"></span>
                    </span>
                </div>
                <div class="flex justify-between text-emerald-600 font-extrabold" x-show="discountAmount > 0">
                    <span>{{ $isId ? 'Diskon Promo' : 'Promo Discount' }}:</span>
                    <span class="font-mono">
                        -Rp <span x-text="formatNumber(discountAmount)"></span>
                    </span>
                </div>
            </div>

            <div class="border-t border-stone-100 pt-3 flex justify-between items-center text-xs font-extrabold text-stone-900 uppercase">
                <span>{{ $isId ? 'Total Bayar' : 'Total Price' }}:</span>
                <span class="text-[#c9512d] font-mono text-sm font-black">
                    Rp <span x-text="formatNumber(Math.max(0, servicePrice - discountAmount))"></span>
                </span>
            </div>
        </div>

    </div>

    <!-- Service Detail Modal -->
    <div x-show="activeDetailService" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="service-detail-modal-title" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="closeServiceModal()"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs transition-opacity" 
             @click="closeServiceModal()"></div>

        <!-- Modal Dialog Positioning -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-stone-200"
                 @click.away="closeServiceModal()"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <template x-if="activeDetailService">
                    <div>
                        <!-- Header with subtle luxury dark styling -->
                        <div class="bg-stone-900 px-6 py-5 text-white flex items-center justify-between border-b border-stone-800">
                            <div>
                                <span class="text-[9px] uppercase tracking-widest text-[#c9512d] font-bold block"
                                      x-text="activeDetailService.category ? activeDetailService.category.name : 'MORE HAIR STUDIO'"></span>
                                <h3 class="text-base sm:text-lg font-bold uppercase tracking-tight text-white mt-0.5" 
                                    id="service-detail-modal-title" 
                                    x-text="activeDetailService.name"></h3>
                            </div>
                            <button type="button" 
                                    @click="closeServiceModal()"
                                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition"
                                    title="{{ $isId ? 'Tutup' : 'Close' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-6 space-y-5 max-h-[65vh] overflow-y-auto">
                            <!-- Quick specs badge row -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-stone-50 border border-stone-200 rounded-2xl p-4">
                                    <span class="text-[9px] uppercase tracking-wider text-stone-400 font-bold block">{{ $isId ? 'Durasi Layanan' : 'Service Duration' }}</span>
                                    <span class="text-sm sm:text-base font-bold text-stone-900 mt-1 block">
                                        <span x-text="getServiceDuration(activeDetailService)"></span> {{ $isId ? 'Menit' : 'Min' }}
                                    </span>
                                </div>
                                <div class="bg-[#faede7]/40 border border-[#c9512d]/20 rounded-2xl p-4">
                                    <span class="text-[9px] uppercase tracking-wider text-[#c9512d] font-bold block">{{ $isId ? 'Investasi / Tarif' : 'Investment' }}</span>
                                    <span class="text-sm sm:text-base font-bold text-[#c9512d] mt-1 block">
                                        Rp <span x-text="formatNumber(getServicePrice(activeDetailService))"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Service Description & Inclusions -->
                            <div class="space-y-2">
                                <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold">
                                    {{ $isId ? 'Rangkaian & Keterangan Layanan' : 'Service Overview & Inclusions' }}
                                </label>
                                <div class="service-description"
                                     x-html="activeDetailService.formatted_description_html || activeDetailService.description || '{{ $isId ? 'Pelayanan potong dan styling rambut profesional dengan standar estetika tinggi khas MORE Hair Studio.' : 'Professional haircut and styling experience tailored to your authentic character.' }}'">
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer Action -->
                        <div class="bg-stone-50 px-6 py-4 border-t border-stone-100 flex items-center justify-between gap-3">
                            <button type="button" 
                                    @click="closeServiceModal()" 
                                    class="px-5 py-2.5 rounded-xl border border-stone-200 text-xs font-bold text-stone-600 hover:bg-stone-100 transition">
                                {{ $isId ? 'Tutup' : 'Close' }}
                            </button>
                            <button type="button" 
                                    @click="selectService(activeDetailService.id); closeServiceModal()" 
                                    class="flex-1 px-5 py-2.5 rounded-xl bg-[#c9512d] hover:bg-[#a03b1e] text-xs font-bold text-white shadow-sm transition flex items-center justify-center gap-2">
                                <span>{{ $isId ? 'Pilih Layanan Ini' : 'Select This Experience' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function bookingWizard() {
    return {
        // State
        step: 1,
        activeDetailService: null,
        isWalkIn: {{ $walkIn ? 'true' : 'false' }},
        preselectedOutletId: {{ $preselectedOutletId ? (int)$preselectedOutletId : ($outlets->count() === 1 ? $outlets->first()->id : 'null') }},
        preselectedServiceId: {{ $preselectedServiceId ? (int)$preselectedServiceId : 'null' }},
        preselectedStylistId: {{ $preselectedStylistId ? (int)$preselectedStylistId : 'null' }},

        // Selection
        selectedOutletId: {{ $preselectedOutletId ? (int)$preselectedOutletId : ($outlets->count() === 1 ? $outlets->first()->id : 'null') }},
        selectedServiceId: {{ $preselectedServiceId ? (int)$preselectedServiceId : 'null' }},
        selectedStylistId: {{ $preselectedStylistId ? (int)$preselectedStylistId : 'null' }},
        selectedDate: '{{ \Carbon\Carbon::today()->toDateString() }}',
        selectedTime: {{ $walkIn ? 'true' : 'false' }} ? '{{ \Carbon\Carbon::now()->format('H:i') }}' : null,

        // Manual & Flexible Time Selection State
        manualTime: '{{ \Carbon\Carbon::now()->format('H:i') }}',
        isTimeValid: true,
        timeConflictMessage: '',
        busyIntervals: {},
        freeWindows: {},
        workingHours: {},
        liveStatus: {},
        showAllStylistsSchedule: false,
        calYear: {{ \Carbon\Carbon::today()->year }},
        calMonth: {{ \Carbon\Carbon::today()->month }},
        leadTimeHours: 1,
        serverTime: '{{ \Carbon\Carbon::now()->format('H:i:s') }}',
        todayDate: '{{ \Carbon\Carbon::today()->toDateString() }}',

        // Customer details
        phone: '',
        customerName: '',
        email: '',
        birthDate: '',
        gender: '',
        notes: '',

        // Promo
        promoCode: '',
        discountAmount: 0,
        promoError: null,
        promoSuccess: null,

        // Payment
        paymentMethod: 'manual',

        // Loaded data
        outlets: @json($outlets),
        categories: @json($categories),
        services: @json($services),
        stylists: @json($stylists),
        slots: {}, // slots mapping per stylist
        loadingSlots: false,
        autoFillSuccess: null,

        getCurrentTimeStr() {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            return `${hh}:${mm}`;
        },

        // Getters
        get selectedOutlet() {
            return this.outlets.find(o => o.id == this.selectedOutletId);
        },
        get selectedService() {
            return this.services.find(s => s.id == this.selectedServiceId);
        },
        get selectedStylist() {
            return this.stylists.find(s => s.id == this.selectedStylistId);
        },
        get servicePrice() {
            if (!this.selectedServiceId || !this.selectedOutletId) return 0;
            const s = this.selectedService;
            if (s && s.outlet_overrides && s.outlet_overrides[this.selectedOutletId]) {
                return parseFloat(s.outlet_overrides[this.selectedOutletId].price || s.default_price);
            }
            return s ? parseFloat(s.default_price) : 0;
        },
        get serviceDuration() {
            if (!this.selectedServiceId || !this.selectedOutletId) return 0;
            const s = this.selectedService;
            if (s && s.outlet_overrides && s.outlet_overrides[this.selectedOutletId]) {
                return parseInt(s.outlet_overrides[this.selectedOutletId].duration || s.default_duration);
            }
            return s ? parseInt(s.default_duration) : 0;
        },

        init() {
            if (this.isWalkIn) {
                this.selectedDate = this.todayDate;
                this.manualTime = this.getCurrentTimeStr();
                this.selectedTime = this.manualTime;
                this.isTimeValid = true;
            }

            if (this.preselectedOutletId) {
                this.selectedOutletId = this.preselectedOutletId;
            } else if (this.outlets.length === 1) {
                this.selectedOutletId = this.outlets[0].id;
            }

            if (this.preselectedServiceId) {
                this.selectedServiceId = this.preselectedServiceId;
                this.step = 2;
            }

            if (this.preselectedStylistId) {
                this.selectedStylistId = this.preselectedStylistId;
                if (this.selectedServiceId) {
                    this.step = 3;
                    this.fetchSlots();
                } else {
                    this.step = 1;
                }
            }
            
            // Try loading draft
            const saved = localStorage.getItem('morehair_booking_draft');
            if (saved && !this.preselectedServiceId && !this.preselectedStylistId) {
                try {
                    const draft = JSON.parse(saved);
                    if (draft.selectedOutletId) this.selectedOutletId = draft.selectedOutletId;
                    if (draft.selectedServiceId) this.selectedServiceId = draft.selectedServiceId;
                    if (draft.selectedStylistId) this.selectedStylistId = draft.selectedStylistId;
                    
                    if (!this.isWalkIn) {
                        if (draft.selectedDate) this.selectedDate = draft.selectedDate;
                        if (draft.selectedTime) {
                            this.selectedTime = draft.selectedTime;
                            this.manualTime = draft.selectedTime;
                        }
                        if (draft.manualTime) {
                            this.manualTime = draft.manualTime;
                        }
                    }
                    if (draft.phone) this.phone = draft.phone;
                    if (draft.customerName) this.customerName = draft.customerName;
                    if (draft.email) this.email = draft.email;
                    if (draft.birthDate) this.birthDate = draft.birthDate;
                    if (draft.gender) this.gender = draft.gender;
                    if (draft.notes) this.notes = draft.notes;
                    if (draft.promoCode) this.promoCode = draft.promoCode;
                    if (draft.step) this.step = draft.step;

                    if (this.selectedOutletId && this.selectedServiceId && this.selectedDate) {
                        this.fetchSlots();
                    }
                } catch (e) {
                    console.error('Failed to load draft:', e);
                }
            }

            // Watch for changes to save draft
            this.$watch('selectedOutletId', () => this.saveDraft());
            this.$watch('selectedServiceId', () => this.saveDraft());
            this.$watch('selectedStylistId', () => {
                this.saveDraft();
                if (this.selectedOutletId && this.selectedServiceId && this.selectedDate) {
                    this.fetchSlots();
                }
            });
            this.$watch('selectedDate', () => {
                this.saveDraft();
                if (this.selectedOutletId && this.selectedServiceId && this.selectedDate) {
                    this.fetchSlots();
                }
            });
            this.$watch('selectedTime', () => this.saveDraft());
            this.$watch('manualTime', () => {
                this.saveDraft();
                this.validateManualTime();
            });
            this.$watch('phone', () => {
                this.saveDraft();
                this.lookupCustomer();
            });
            this.$watch('customerName', () => this.saveDraft());
            this.$watch('email', () => this.saveDraft());
            this.$watch('birthDate', () => this.saveDraft());
            this.$watch('gender', () => this.saveDraft());
            this.$watch('notes', () => this.saveDraft());
            this.$watch('promoCode', () => this.saveDraft());
            this.$watch('step', () => {
                this.saveDraft();
                const el = document.getElementById('booking-wizard-container');
                if (el) {
                    const navHeight = 90;
                    const y = el.getBoundingClientRect().top + window.pageYOffset - navHeight;
                    window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
                }
            });
        },

        saveDraft() {
            const draft = {
                step: this.step,
                selectedOutletId: this.selectedOutletId,
                selectedServiceId: this.selectedServiceId,
                selectedStylistId: this.selectedStylistId,
                selectedDate: this.selectedDate,
                selectedTime: this.selectedTime,
                manualTime: this.manualTime,
                phone: this.phone,
                customerName: this.customerName,
                email: this.email,
                birthDate: this.birthDate,
                gender: this.gender,
                notes: this.notes,
                promoCode: this.promoCode
            };
            localStorage.setItem('morehair_booking_draft', JSON.stringify(draft));
        },

        selectOutlet(id) {
            this.selectedOutletId = id;
            this.selectedServiceId = null;
            this.selectedStylistId = null;
            this.selectedTime = null;
        },

        selectService(id) {
            this.selectedServiceId = id;
            if (this.preselectedStylistId && this.selectedStylistId) {
                if (this.isWalkIn) {
                    this.selectedDate = this.todayDate;
                    this.manualTime = this.getCurrentTimeStr();
                    this.selectedTime = this.manualTime;
                    this.isTimeValid = true;
                } else {
                    this.selectedTime = null;
                }
                this.step = 3;
                this.fetchSlots();
            } else {
                this.selectedStylistId = null;
                this.selectedTime = null;
                this.step = 2;
            }
        },

        changeStylist() {
            this.preselectedStylistId = null;
            this.selectedStylistId = null;
            this.selectedTime = null;
            this.step = 2;
        },

        selectStylist(id) {
            this.selectedStylistId = id;
            if (this.isWalkIn) {
                this.selectedDate = this.todayDate;
                this.manualTime = this.getCurrentTimeStr();
                this.selectedTime = this.manualTime;
                this.isTimeValid = true;
            } else {
                this.selectedTime = null;
            }
            this.step = 3;
            this.fetchSlots();
        },

        selectTime(time) {
            this.selectedTime = time;
            this.step = 4;
        },

        selectStylistAndSlot(stylistId, time) {
            this.selectedStylistId = stylistId;
            this.selectedTime = time;
            this.step = 4;
        },

        prevStep() {
            if (this.step === 3 && this.preselectedStylistId) {
                this.step = 1;
                return;
            }
            if (this.step > 1) {
                this.step--;
            }
        },

        openServiceModal(service) {
            this.activeDetailService = service;
        },

        closeServiceModal() {
            this.activeDetailService = null;
        },

        handleTopBack() {
            if (this.step > 1) {
                this.prevStep();
            } else {
                if (window.history.length > 1 && document.referrer) {
                    window.history.back();
                } else {
                    window.location.href = '{{ route('home') }}';
                }
            }
        },

        getBackStepLabel() {
            const isId = {{ $isId ? 'true' : 'false' }};
            if (this.step === 1) return isId ? 'Kembali ke Halaman Sebelumnya' : 'Back to Previous Page';
            if (this.step === 2) return isId ? 'Kembali ke Pilih Layanan' : 'Back to Choose Experience';
            if (this.step === 3) {
                if (this.preselectedStylistId) return isId ? 'Kembali ke Pilih Layanan' : 'Back to Choose Experience';
                return isId ? 'Kembali ke Pilih Stylist' : 'Back to Meet Barber';
            }
            if (this.step === 4) return isId ? 'Kembali ke Pilih Waktu' : 'Back to Choose Time';
            return isId ? 'Kembali' : 'Back';
        },

        getServicesByCategory(catId) {
            return this.services.filter(s => s.service_category_id == catId);
        },

        getServicePrice(service) {
            if (!this.selectedOutletId) return service.default_price;
            if (service.outlet_overrides && service.outlet_overrides[this.selectedOutletId]) {
                return service.outlet_overrides[this.selectedOutletId].price || service.default_price;
            }
            return service.default_price;
        },

        getServiceDuration(service) {
            if (!this.selectedOutletId) return service.default_duration;
            if (service.outlet_overrides && service.outlet_overrides[this.selectedOutletId]) {
                return service.outlet_overrides[this.selectedOutletId].duration || service.default_duration;
            }
            return service.default_duration;
        },

        getStylistsByOutlet() {
            if (!this.selectedOutletId) return [];
            return this.stylists.filter(st => st.outlet_id == this.selectedOutletId);
        },

        getSelectedStylistSlots() {
            if (!this.selectedStylistId) return [];
            return this.slots[this.selectedStylistId] || [];
        },

        getAlternativeStylists() {
            if (!this.selectedStylistId) return [];
            return this.getStylistsByOutlet().filter(st => st.id != this.selectedStylistId);
        },

        get calMonthLabel() {
            const date = new Date(this.calYear, this.calMonth - 1, 1);
            return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        },

        get calDays() {
            const year = this.calYear;
            const month = this.calMonth - 1; // 0-indexed
            
            const firstDayIndex = new Date(year, month, 1).getDay(); // 0 = Sunday
            const totalDays = new Date(year, month + 1, 0).getDate();
            
            const todayStr = this.todayDate || '{{ \Carbon\Carbon::today()->toDateString() }}';
            const blanks = [];
            for (let i = 0; i < firstDayIndex; i++) {
                blanks.push(i);
            }

            const days = [];
            for (let d = 1; d <= totalDays; d++) {
                const mm = String(month + 1).padStart(2, '0');
                const dd = String(d).padStart(2, '0');
                const dateStr = `${year}-${mm}-${dd}`;
                
                days.push({
                    dayNumber: d,
                    dateStr: dateStr,
                    isToday: dateStr === todayStr,
                    isPast: dateStr < todayStr
                });
            }

            return { blanks, days };
        },

        prevCalMonth() {
            if (this.calMonth === 1) {
                this.calMonth = 12;
                this.calYear--;
            } else {
                this.calMonth--;
            }
        },

        nextCalMonth() {
            if (this.calMonth === 12) {
                this.calMonth = 1;
                this.calYear++;
            } else {
                this.calMonth++;
            }
        },

        setWizardDate(dateStr) {
            this.selectedDate = dateStr;
            const parts = dateStr.split('-');
            this.calYear = parseInt(parts[0]);
            this.calMonth = parseInt(parts[1]);
            this.fetchSlots();
        },

        getSelectedStylistBusyIntervals() {
            if (!this.selectedStylistId) return [];
            return this.busyIntervals[this.selectedStylistId] || [];
        },

        getSelectedStylistFreeWindows() {
            if (!this.selectedStylistId) return [];
            return this.freeWindows[this.selectedStylistId] || [];
        },

        isStylistWorkingOnDate(stylistId) {
            const wh = this.workingHours[stylistId];
            return wh ? Boolean(wh.is_working) : false;
        },

        getStylistWorkingHoursText(stylistId) {
            const wh = this.workingHours[stylistId];
            if (!wh || !wh.is_working) return 'Libur';
            return wh.start_time + ' - ' + wh.end_time + ' WIB';
        },

        isStylistBusyNow(id) {
            if (!id || !this.liveStatus[id]) return false;
            return Boolean(this.liveStatus[id].is_busy);
        },

        getStylistActiveService(id) {
            return this.liveStatus[id]?.service_name || 'Layanan Rambut';
        },

        getStylistFinishTime(id) {
            return this.liveStatus[id]?.finish_time || '--:--';
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },

        async fetchSlots() {
            if (!this.selectedOutletId || !this.selectedServiceId || !this.selectedDate) return;
            this.loadingSlots = true;
            try {
                const res = await fetch(`/booking/slots?outlet_id=${this.selectedOutletId}&service_id=${this.selectedServiceId}&date=${this.selectedDate}&walk_in=${this.isWalkIn ? 1 : 0}`);
                const data = await res.json();
                this.slots = data.slots || {};
                this.busyIntervals = data.busy_intervals || {};
                this.freeWindows = data.free_windows || {};
                this.workingHours = data.working_hours || {};
                this.liveStatus = data.live_status || {};
                this.leadTimeHours = data.lead_time_hours || 1;
                this.serverTime = data.server_time || '';
                this.todayDate = data.today_date || '';

                if (this.isWalkIn) {
                    this.manualTime = this.getCurrentTimeStr();
                    this.selectedTime = this.manualTime;
                    this.isTimeValid = true;
                } else {
                    // If stylist is selected and has available slots, default manualTime to first available slot if not set
                    const available = this.getSelectedStylistSlots();
                    if (available && available.length > 0 && (!this.manualTime || this.manualTime === '10:00')) {
                        this.manualTime = available[0].time;
                    }
                }
                this.validateManualTime();
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingSlots = false;
            }
        },

        timeToMinutes(t) {
            if (!t) return 0;
            const parts = t.split(':');
            return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
        },

        minutesToTime(m) {
            const h = Math.floor(m / 60) % 24;
            const min = m % 60;
            return String(h).padStart(2, '0') + ':' + String(min).padStart(2, '0');
        },

        calculateEndTime(startTime) {
            if (!startTime) return '--:--';
            const dur = this.serviceDuration || 45;
            const startM = this.timeToMinutes(startTime);
            return this.minutesToTime(startM + dur);
        },

        onManualTimeChange() {
            this.validateManualTime();
        },

        pickRecommendedSlot(time) {
            this.manualTime = time;
            this.selectedTime = time;
            this.validateManualTime();
        },

        confirmTimeAndProceed() {
            if (this.isWalkIn) {
                this.manualTime = this.getCurrentTimeStr();
                this.selectedTime = this.manualTime;
                this.selectedDate = this.todayDate;
                this.isTimeValid = true;
                this.step = 4;
                return;
            }
            this.validateManualTime();
            if (this.isTimeValid && this.manualTime) {
                this.selectedTime = this.manualTime;
                this.step = 4;
            }
        },

        validateManualTime() {
            if (this.isWalkIn) {
                this.isTimeValid = true;
                this.timeConflictMessage = '';
                return;
            }

            if (!this.manualTime) {
                this.isTimeValid = false;
                this.timeConflictMessage = 'Silakan masukkan jam sesi.';
                return;
            }

            const stylistId = this.selectedStylistId;
            const dur = this.serviceDuration || 45;
            const startM = this.timeToMinutes(this.manualTime);
            const endM = startM + dur;
            const stylist = this.selectedStylist;
            const stylistName = stylist ? stylist.name : 'Stylist';

            // 1. Check working hours
            const wh = this.workingHours[stylistId];
            if (wh) {
                if (!wh.is_working) {
                    this.isTimeValid = false;
                    this.timeConflictMessage = `${stylistName} tidak bertugas pada tanggal ini. Silakan ganti tanggal atau stylist.`;
                    return;
                }
                const workStartM = this.timeToMinutes(wh.start_time);
                const workEndM = this.timeToMinutes(wh.end_time);

                if (startM < workStartM || endM > workEndM) {
                    this.isTimeValid = false;
                    this.timeConflictMessage = `Jam operasional ${stylistName} adalah ${wh.start_time} - ${wh.end_time} WIB. Sesi ${this.manualTime} - ${this.calculateEndTime(this.manualTime)} harus berada dalam rentang tersebut.`;
                    return;
                }
            }

            // 2. Check past time and lead time if today
            const today = this.todayDate || new Date().toISOString().split('T')[0];
            if (this.selectedDate === today) {
                const now = new Date();
                const nowMinutes = now.getHours() * 60 + now.getMinutes();
                const leadMinutes = this.isWalkIn ? 0 : ((this.leadTimeHours || 1) * 60);

                if (this.isWalkIn) {
                    // 15-minute grace tolerance for walk-ins
                    if (startM < (nowMinutes - 15)) {
                        this.isTimeValid = false;
                        this.timeConflictMessage = 'Jam sesi walk-in lebih dari 15 menit yang lalu.';
                        return;
                    }
                } else {
                    if (startM < nowMinutes) {
                        this.isTimeValid = false;
                        this.timeConflictMessage = 'Jam sesi ini sudah terlewat untuk hari ini.';
                        return;
                    }

                    if (startM < (nowMinutes + leadMinutes)) {
                        this.isTimeValid = false;
                        const minAllowed = this.minutesToTime(nowMinutes + leadMinutes);
                        this.timeConflictMessage = `Pemesanan online untuk hari ini minimal ${this.leadTimeHours} jam sebelum sesi (minimal jam ${minAllowed} WIB).`;
                        return;
                    }
                }
            }

            // 3. Continuous interval overlap check: (startM < bEndM) && (endM > bStartM)
            const intervals = this.busyIntervals[stylistId] || [];
            for (let item of intervals) {
                const bStartM = this.timeToMinutes(item.start);
                const bEndM = this.timeToMinutes(item.end);

                if (startM < bEndM && endM > bStartM) {
                    this.isTimeValid = false;
                    this.timeConflictMessage = `${stylistName} sudah memiliki sesi booking jam ${item.start} - ${item.end} WIB. Sesi ${this.manualTime} - ${this.calculateEndTime(this.manualTime)} bertabrakan.`;
                    return;
                }
            }

            // If all checks pass
            this.isTimeValid = true;
            this.timeConflictMessage = '';
        },

        async lookupCustomer() {
            if (this.phone.length < 9) return;
            try {
                const res = await fetch(`/booking/customer-lookup?phone=${encodeURIComponent(this.phone)}`);
                const data = await res.json();
                if (data.found) {
                    this.customerName = data.customer.name;
                    this.email = data.customer.email;
                    this.birthDate = data.customer.birth_date;
                    this.gender = data.customer.gender;
                    this.autoFillSuccess = 'Data profil ditemukan dan terisi otomatis!';
                }
            } catch (e) {
                console.error(e);
            }
        },

        async applyPromo() {
            if (!this.promoCode) return;
            try {
                const res = await fetch(`/booking/apply-promo?promo_code=${encodeURIComponent(this.promoCode)}&service_price=${this.servicePrice}`);
                const data = await res.json();
                if (data.success) {
                    this.discountAmount = data.discount;
                    this.promoSuccess = data.message;
                    this.promoError = null;
                } else {
                    this.discountAmount = 0;
                    this.promoError = data.message;
                    this.promoSuccess = null;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async confirmBooking() {
            if (!this.phone || this.phone.length < 9) {
                alert('Silakan masukkan nomor telepon WhatsApp yang valid.');
                return;
            }
            if (!this.customerName || this.customerName.length < 3) {
                alert('Silakan masukkan nama lengkap.');
                return;
            }

            try {
                const res = await fetch('/booking/confirm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        phone: this.phone,
                        customer_name: this.customerName,
                        email: this.email,
                        birth_date: this.birthDate,
                        gender: this.gender,
                        outlet_id: this.selectedOutletId,
                        service_id: this.selectedServiceId,
                        stylist_id: this.selectedStylistId,
                        booking_date: this.isWalkIn ? this.todayDate : this.selectedDate,
                        booking_time: this.isWalkIn ? (this.selectedTime || this.manualTime || this.getCurrentTimeStr()) : this.selectedTime,
                        promo_code: this.promoCode,
                        payment_method: this.paymentMethod,
                        notes: this.notes,
                        is_walk_in: this.isWalkIn
                    })
                });

                const data = await res.json();
                if (data.success) {
                    localStorage.removeItem('morehair_booking_draft');
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'Gagal memproses booking. Silakan coba lagi.');
                }
            } catch (e) {
                console.error(e);
                alert('Gagal menghubungi server.');
            }
        }
    };
}
</script>
@endsection
