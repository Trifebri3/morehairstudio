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
    <div class="lg:col-span-2 space-y-8">
        
        <!-- Premium Stepper (4 steps) -->
        <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between relative">
                <!-- Connective timeline bar -->
                <div class="absolute left-6 right-6 top-5 h-0.5 bg-stone-100 -z-10"></div>

                @foreach($steps as $num => $label)
                    <div class="flex flex-col items-center flex-1 relative z-10">
                        <!-- Step Badge Circle -->
                        <template x-if="step > {{ $num }}">
                            <div class="h-10 w-10 rounded-lg bg-[#1c1917] text-white flex items-center justify-center font-bold text-sm shadow-sm select-none">
                                ✓
                            </div>
                        </template>
                        <template x-if="step === {{ $num }}">
                            <div class="h-10 w-10 rounded-lg border-2 border-blue-50 bg-[#0A3D91] text-white flex items-center justify-center font-bold text-sm shadow-sm select-none">
                                0{{ $num }}
                            </div>
                        </template>
                        <template x-if="step < {{ $num }}">
                            <div class="h-10 w-10 rounded-lg bg-stone-100 text-stone-400 border border-stone-200 flex items-center justify-center font-bold text-sm select-none">
                                0{{ $num }}
                            </div>
                        </template>
                        
                        <!-- Step Label -->
                        <span class="text-[9px] sm:text-[10px] uppercase tracking-wider font-extrabold mt-3 text-center"
                              :class="step === {{ $num }} ? 'text-[#0A3D91]' : 'text-stone-400'">
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

                <!-- 1A: Outlet Selector -->
                <div class="space-y-6">
                    <div x-show="!isWalkIn">
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-4">
                            {{ $isId ? '01. Pilih Lokasi Studio' : '01. Select Location' }}
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <template x-for="outlet in outlets" :key="outlet.id">
                                <div class="border rounded-2xl overflow-hidden cursor-pointer transition flex flex-col justify-between"
                                     :class="selectedOutletId == outlet.id ? 'border-[#0A3D91] bg-blue-50/20 ring-1 ring-[#0A3D91]/30 shadow-sm' : 'border-stone-200 hover:border-[#0A3D91]/50'"
                                     @click="selectOutlet(outlet.id)">
                                    <!-- Outlet image placeholder/generic -->
                                    <div class="h-40 w-full overflow-hidden bg-stone-50">
                                        <img :src="'/images/outlet_' + outlet.id + '.jpg'" onerror="this.src='https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=500'" :alt="outlet.name" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-5 flex-grow">
                                        <h4 class="font-bold text-stone-800 text-xs uppercase tracking-wider" x-text="outlet.name"></h4>
                                        <span class="text-[10px] text-stone-400 block mt-1 leading-relaxed font-light">Address: <span x-text="outlet.address"></span></span>
                                    </div>
                                    <div x-show="selectedOutletId == outlet.id" class="bg-[#0A3D91] text-white text-[9px] uppercase font-extrabold tracking-widest text-center py-2">
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
                                            <div class="border border-stone-200 rounded-xl p-5 bg-white hover:border-[#0A3D91] hover:shadow-sm transition cursor-pointer flex justify-between items-center"
                                                 @click="selectService(service.id)">
                                                <div>
                                                    <h5 class="font-bold text-stone-900 text-xs uppercase tracking-tight" x-text="service.name"></h5>
                                                    <div class="text-[10px] text-stone-500 mt-1 flex items-center space-x-2">
                                                        <span>Duration: <span x-text="getServiceDuration(service)"></span> Min</span>
                                                        <span>•</span>
                                                        <span class="text-[#0A3D91] hover:underline font-bold">Details</span>
                                                        <span>•</span>
                                                        <span class="font-bold">Rp <span x-text="formatNumber(getServicePrice(service))"></span></span>
                                                    </div>
                                                </div>
                                                <span class="text-stone-300 text-xs">&#10095;</span>
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
                        <div class="border border-stone-200 rounded-xl p-5 bg-white hover:border-[#0A3D91] hover:shadow-sm transition cursor-pointer flex items-center space-x-4"
                             @click="selectStylist(stylist.id)">
                            <div class="h-12 w-12 rounded-lg overflow-hidden border border-stone-200 shadow-inner flex-shrink-0 bg-stone-50 flex items-center justify-center">
                                <img :src="'https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(stylist.slug)" :alt="stylist.name" class="h-full w-full object-cover">
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-bold text-stone-900 text-xs uppercase tracking-tight" x-text="stylist.name"></h4>
                                <span class="text-[9px] text-[#0A3D91] uppercase font-extrabold tracking-wider block mt-0.5" x-text="stylist.specialization"></span>
                                <p class="text-stone-450 italic text-[10px] leading-relaxed mt-2">
                                    "Every service is a collaboration to find a style that expresses who you are."
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Step 3: Choose Date & Time -->
            <div x-show="step === 3" x-transition>
                <div class="mb-8 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-stone-900 uppercase tracking-tight">
                        {{ $isId ? 'Pilih Tanggal & Jam Sesi' : 'Choose Date & Time' }}
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $isId ? 'Tentukan tanggal kunjungan dan jam sesi Anda.' : 'Select your visit date and preferred session slot.' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Date Selector -->
                    <div class="lg:col-span-1 space-y-4">
                        <div class="border border-stone-200 rounded-xl p-5 bg-white shadow-sm">
                            <label class="block text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold mb-3">
                                {{ $isId ? '01. PILIH TANGGAL' : '01. SELECT DATE' }}
                            </label>
                            <input type="date" 
                                   class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 rounded-xl text-xs font-mono text-stone-900 focus:outline-none focus:border-[#0A3D91] transition"
                                   x-model="selectedDate"
                                   min="{{ \Carbon\Carbon::today()->toDateString() }}" />
                        </div>
                    </div>

                    <!-- Right Column: Slots Availability -->
                    <div class="lg:col-span-2 space-y-6">
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">
                            {{ $isId ? 'JADWAL SESI & KETERSEDIAAN STYLIST PADA TANGGAL INI' : 'SESSION SCHEDULE & STYLIST AVAILABILITY FOR THIS DATE' }}
                        </label>
                        
                        <div x-show="loadingSlots" class="text-xs font-bold text-[#0A3D91] py-4">
                            Memuat ketersediaan slot...
                        </div>

                        <!-- Preferred Stylist Card -->
                        <div x-show="!loadingSlots && selectedStylist" class="border rounded-2xl p-6 bg-white border-[#0A3D91] ring-1 ring-[#0A3D91]/10 shadow-sm flex flex-col gap-6">
                            <!-- Top Part: Stylist Profile -->
                            <div class="flex items-center space-x-4">
                                <div class="h-12 w-12 rounded-lg overflow-hidden border border-stone-200 shadow-inner flex-shrink-0 bg-stone-50 flex items-center justify-center">
                                    <img :src="'https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(selectedStylist ? selectedStylist.slug : '')" class="h-full w-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h4 class="font-bold text-stone-900 text-xs uppercase tracking-tight" x-text="selectedStylist ? selectedStylist.name : ''"></h4>
                                        <span class="text-[8px] uppercase tracking-wider bg-blue-50 text-[#0A3D91] px-2 py-0.5 rounded font-extrabold">{{ $isId ? 'Pilihan Utama' : 'Preferred' }}</span>
                                    </div>
                                    <span class="text-[9px] text-stone-400 uppercase font-extrabold tracking-wider block mt-0.5" x-text="selectedStylist ? selectedStylist.specialization : ''"></span>
                                </div>
                            </div>

                            <!-- Bottom Part: Slots Grid -->
                            <div class="pt-4 border-t border-stone-100">
                                <template x-if="getSelectedStylistSlots().length === 0">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[9px] uppercase tracking-widest font-extrabold bg-red-50 border border-red-100 text-red-500 select-none">
                                        {{ $isId ? 'Habis / Sold Out' : 'Fully Booked' }}
                                    </span>
                                </template>
                                <template x-if="getSelectedStylistSlots().length > 0">
                                    <div>
                                        <label class="block text-[9px] uppercase tracking-widest text-stone-400 font-extrabold mb-3">
                                            {{ $isId ? 'Pilih Jam Sesi Yang Tersedia' : 'Select Available Session Slot' }}
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="slot in getSelectedStylistSlots()" :key="slot.time">
                                                <button type="button" 
                                                        class="py-2.5 px-4 min-w-[75px] text-center rounded-lg border text-xs font-bold font-mono transition"
                                                        :class="selectedTime == slot.time ? 'border-[#0A3D91] bg-blue-50/20 text-[#0A3D91]' : 'border-stone-200 text-stone-700 hover:border-[#0A3D91]'"
                                                        @click="selectTime(slot.time)">
                                                    <span x-text="slot.time"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Alternative Stylist Suggestion (only shown if preferred stylist is sold out) -->
                        <div x-show="!loadingSlots && getSelectedStylistSlots().length === 0" class="pt-6 border-t border-stone-200 space-y-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold uppercase tracking-tight text-stone-900">
                                    {{ $isId ? 'Rekomendasi Alternatif Stylist Lain Yang Tersedia' : 'Recommended Available Alternative Stylists' }}
                                </span>
                            </div>
                            
                            <div class="space-y-4">
                                <template x-for="altStylist in getAlternativeStylists()" :key="altStylist.id">
                                    <div class="border border-stone-200 rounded-2xl p-6 bg-white hover:shadow-sm transition flex flex-col gap-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="h-12 w-12 rounded-lg bg-stone-50 text-stone-450 font-extrabold text-sm select-none flex items-center justify-center border border-stone-200">
                                                <span x-text="altStylist.name.charAt(0)"></span>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-stone-900 text-xs uppercase tracking-tight" x-text="altStylist.name"></h4>
                                                <span class="text-[9px] text-stone-400 uppercase font-extrabold tracking-wider block mt-0.5" x-text="altStylist.specialization"></span>
                                            </div>
                                        </div>

                                        <div class="pt-4 border-t border-stone-100">
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="slot in slots[altStylist.id]" :key="slot.time">
                                                    <button type="button" 
                                                            class="py-2.5 px-4 min-w-[75px] rounded-lg border bg-white text-xs font-bold font-mono transition text-center border-stone-200 text-stone-700 hover:border-[#0A3D91]"
                                                            @click="selectStylistAndSlot(altStylist.id, slot.time)">
                                                        <span x-text="slot.time"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-ui.input label="Email" type="email" placeholder="john@example.com" x-model="email" />
                            <x-ui.input label="{{ $isId ? 'Tanggal Lahir' : 'Birth Date' }}" type="date" x-model="birthDate" />
                            
                            <div>
                                <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Gender</label>
                                <select x-model="gender" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 rounded-xl text-xs text-stone-900 focus:outline-none focus:border-[#0A3D91] transition">
                                    <option value="">Pilih Gender</option>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Code -->
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
                        <div class="border border-stone-200 rounded-xl p-6 bg-white space-y-4" x-show="!isWalkIn">
                            <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-stone-400 border-b pb-2">03. Metode Pembayaran</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border rounded-lg p-4 cursor-pointer text-center"
                                     :class="paymentMethod === 'manual' ? 'border-[#0A3D91] bg-blue-50/20' : 'border-stone-200'"
                                     @click="paymentMethod = 'manual'">
                                    <span class="text-xs font-bold block text-stone-850 uppercase">Bayar di Outlet</span>
                                </div>
                                <div class="border rounded-lg p-4 cursor-pointer text-center"
                                     :class="paymentMethod === 'midtrans' ? 'border-[#0A3D91] bg-blue-50/20' : 'border-stone-200'"
                                     @click="paymentMethod = 'midtrans'">
                                    <span class="text-xs font-bold block text-stone-850 uppercase">Bayar Sekarang (Online)</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="pt-4 flex justify-between items-center">
                        <button type="button" @click="prevStep" class="px-4 py-2 border border-stone-200 rounded-xl text-stone-600 hover:bg-stone-50 font-bold text-xs">Kembali</button>
                        <x-ui.button variant="primary" size="lg" type="button" @click="confirmBooking">
                            Book your experience
                        </x-ui.button>
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

            <!-- Rating summary -->
            <div class="flex items-center justify-center space-x-2 mt-2 text-xxs text-stone-400 font-extrabold uppercase tracking-widest">
                <span class="text-stone-850">Rating: 5.0</span>
                <span>•</span>
                <span>229 reviews</span>
            </div>

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
                <div class="flex justify-between" x-show="selectedTime">
                    <span>Session Time:</span>
                    <span class="text-stone-850 font-bold font-mono" x-text="selectedTime"></span>
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
                <span class="text-[#0A3D91] font-mono text-sm font-black">
                    Rp <span x-text="formatNumber(Math.max(0, servicePrice - discountAmount))"></span>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
function bookingWizard() {
    return {
        // State
        step: 1,
        isWalkIn: {{ $walkIn ? 'true' : 'false' }},
        preselectedOutletId: {{ $preselectedOutletId ? (int)$preselectedOutletId : 'null' }},

        // Selection
        selectedOutletId: null,
        selectedServiceId: null,
        selectedStylistId: null,
        selectedDate: '{{ \Carbon\Carbon::now()->toDateString() }}',
        selectedTime: null,

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
            if (this.preselectedOutletId) {
                this.selectedOutletId = this.preselectedOutletId;
            }
            
            // Try loading draft
            const saved = localStorage.getItem('morehair_booking_draft');
            if (saved) {
                try {
                    const draft = JSON.parse(saved);
                    if (draft.selectedOutletId) this.selectedOutletId = draft.selectedOutletId;
                    if (draft.selectedServiceId) this.selectedServiceId = draft.selectedServiceId;
                    if (draft.selectedStylistId) this.selectedStylistId = draft.selectedStylistId;
                    if (draft.selectedDate) this.selectedDate = draft.selectedDate;
                    if (draft.selectedTime) this.selectedTime = draft.selectedTime;
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
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
            this.selectedStylistId = null;
            this.selectedTime = null;
            this.step = 2;
        },

        selectStylist(id) {
            this.selectedStylistId = id;
            this.selectedTime = null;
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
            if (this.step > 1) {
                this.step--;
            }
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
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingSlots = false;
            }
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
                        booking_date: this.selectedDate,
                        booking_time: this.selectedTime,
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
