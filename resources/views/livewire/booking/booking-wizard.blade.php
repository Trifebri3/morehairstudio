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
     x-data="{
        step: @entangle('currentStep')
     }"
     x-init="
        // Watch step changes to scroll to top smoothly
        $watch('step', function(value) {
            const el = document.getElementById('booking-wizard-container');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
        
        // Save draft function
        const saveDraft = function() {
            const draft = {
                currentStep: $wire.get('currentStep'),
                selectedOutletId: $wire.get('selectedOutletId'),
                selectedServiceId: $wire.get('selectedServiceId'),
                selectedStylistId: $wire.get('selectedStylistId'),
                selectedDate: $wire.get('selectedDate'),
                selectedTime: $wire.get('selectedTime'),
                phone: $wire.get('phone'),
                customerName: $wire.get('customerName'),
                email: $wire.get('email'),
                birthDate: $wire.get('birthDate'),
                gender: $wire.get('gender'),
                notes: $wire.get('notes'),
                promoCode: $wire.get('promoCode')
            };
            localStorage.setItem('morehair_booking_draft', JSON.stringify(draft));
        };

        // Listen to changes to save draft
        window.addEventListener('input', function() {
            clearTimeout(window.saveDraftTimeout);
            window.saveDraftTimeout = setTimeout(saveDraft, 500);
        });
        window.addEventListener('change', saveDraft);
        
        // Watch structural parameters directly
        $watch('$wire.selectedOutletId', saveDraft);
        $watch('$wire.selectedServiceId', saveDraft);
        $watch('$wire.selectedStylistId', saveDraft);
        $watch('$wire.selectedDate', saveDraft);
        $watch('$wire.selectedTime', saveDraft);
        $watch('$wire.currentStep', saveDraft);
        
        // Load draft on init
        const saved = localStorage.getItem('morehair_booking_draft');
        if (saved) {
            const draft = JSON.parse(saved);
            if (draft.selectedOutletId || draft.selectedServiceId) {
                $wire.dispatch('restoreDraft', { draft: draft });
            }
        }
     }"
     x-on:clear-draft.window="localStorage.removeItem('morehair_booking_draft')">
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
                        @if($currentStep > $num)
                            <!-- Completed -->
                            <div class="h-10 w-10 rounded-lg bg-[#1c1917] text-white flex items-center justify-center font-bold text-sm shadow-sm select-none">
                                ✓
                            </div>
                        @elseif($currentStep === $num)
                            <!-- Active -->
                            <div class="h-10 w-10 rounded-lg border-2 border-blue-50 bg-[#0A3D91] text-white flex items-center justify-center font-bold text-sm shadow-sm select-none">
                                0{{ $num }}
                            </div>
                        @else
                            <!-- Upcoming -->
                            <div class="h-10 w-10 rounded-lg bg-stone-100 text-stone-400 border border-stone-200 flex items-center justify-center font-bold text-sm select-none">
                                0{{ $num }}
                            </div>
                        @endif
                        
                        <!-- Step Label -->
                        <span class="text-[9px] sm:text-[10px] uppercase tracking-wider font-extrabold mt-3 text-center {{ $currentStep === $num ? 'text-[#0A3D91]' : 'text-stone-400' }}">
                            {{ $label }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Step Content Card -->
        <div class="bg-white border border-stone-200 rounded-2xl p-8 shadow-sm min-h-[450px]">
            @if($currentStep === 1)
                <!-- Step 1: Choose Experience -->
                <div class="mb-8 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-stone-900 uppercase tracking-tight">
                        {{ $isId ? 'Bagaimana Anda ingin menikmati More?' : 'How would you like to experience More?' }}
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $isId ? 'Pilih lokasi studio terdekat dan jenis perawatan rambut Anda.' : 'Select your preferred studio location and grooming treatment.' }}
                    </p>
                </div>

                <!-- 1A: Outlet Selector (with images) -->
                <div class="space-y-6">
                    @if(!($isTablet ?? false))
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-4">
                            {{ $isId ? '01. Pilih Lokasi Studio' : '01. Select Location' }}
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($outlets as $outlet)
                                <div class="border rounded-2xl overflow-hidden cursor-pointer transition flex flex-col justify-between {{ $selectedOutletId == $outlet->id ? 'border-[#0A3D91] bg-blue-50/20 ring-1 ring-[#0A3D91]/30 shadow-sm' : 'border-stone-200 hover:border-[#0A3D91]/50' }}"
                                     wire:click="selectOutlet({{ $outlet->id }})">
                                    <!-- Outlet image -->
                                    <div class="h-40 w-full overflow-hidden bg-stone-50">
                                        <img src="/images/outlet_{{ $outlet->id }}.jpg" alt="{{ $outlet->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-5 flex-grow">
                                        <h4 class="font-bold text-stone-850 text-xs uppercase tracking-wider">{{ $outlet->name }}</h4>
                                        <span class="text-[10px] text-stone-400 block mt-1 leading-relaxed font-light">Address: {{ $outlet->address }}</span>
                                    </div>
                                    @if($selectedOutletId == $outlet->id)
                                        <div class="bg-[#0A3D91] text-white text-[9px] uppercase font-extrabold tracking-widest text-center py-2">
                                            {{ $isId ? 'Studio Terpilih' : 'Selected Location' }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- 1B: Service Selector -->
                    @if($selectedOutletId)
                        <div class="{{ ($isTablet ?? false) ? '' : 'pt-6 border-t border-stone-100' }}">
                            <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-4">
                                {{ ($isTablet ?? false) ? ($isId ? 'Pilih Gaya Rambut Anda' : 'Find your style') : ($isId ? '02. Pilih Gaya Rambut Anda' : '02. Find your style') }}
                            </label>
                            
                            <div class="space-y-8" x-data="{ openCat: 0 }">
                                @foreach($categories as $category)
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center border-b border-stone-150 pb-2 cursor-pointer"
                                             @click="openCat = (openCat === {{ $category->id }} ? 0 : {{ $category->id }})">
                                            <h4 class="text-[10px] font-extrabold uppercase tracking-widest text-stone-450">{{ $category->name }}</h4>
                                            <span class="text-stone-400 text-[10px]" x-text="openCat === {{ $category->id }} ? '▲' : '▼'"></span>
                                        </div>
                                        
                                        <div class="space-y-4" x-show="openCat === 0 || openCat === {{ $category->id }}">
                                            @foreach($services->where('service_category_id', $category->id) as $service)
                                                @php
                                                    $itemPrice = $service->outlets->firstWhere('id', $selectedOutletId)->pivot->price ?? $service->default_price;
                                                    $itemDuration = $service->outlets->firstWhere('id', $selectedOutletId)->pivot->duration ?? $service->default_duration;
                                                @endphp
                                                <div class="border border-stone-200 rounded-xl p-5 bg-white hover:border-[#0A3D91] hover:shadow-sm transition cursor-pointer flex justify-between items-center"
                                                     wire:click="selectService({{ $service->id }})">
                                                    <div>
                                                        <h5 class="font-bold text-stone-900 text-xs uppercase tracking-tight">{{ $service->name }}</h5>
                                                        <div class="text-[10px] text-stone-500 mt-1 flex items-center space-x-2">
                                                            <span>Duration: {{ $itemDuration }} Min</span>
                                                            <span>•</span>
                                                            <span class="text-[#0A3D91] hover:underline font-bold">Details</span>
                                                            <span>•</span>
                                                            <span class="font-bold">Rp {{ number_format($itemPrice, 0, ',', '.') }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="text-stone-300 text-xs">&#10095;</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

            @elseif($currentStep === 2)
                <!-- Step 2: Meet Your Barber -->
                <div class="mb-8 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-stone-900 uppercase tracking-tight">
                        {{ $isId ? 'Pilih Stylist Anda' : 'Meet your barber' }}
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $isId ? 'Pilih stylist ahli kami untuk menangani Anda.' : 'Select our expert stylist to design your style.' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($stylists as $stylist)
                        <div class="border border-stone-200 rounded-xl p-5 bg-white hover:border-[#0A3D91] hover:shadow-sm transition cursor-pointer flex items-center space-x-4"
                             wire:click="selectStylist({{ $stylist->id }})">
                            <div class="h-12 w-12 rounded-lg overflow-hidden border border-stone-200 shadow-inner flex-shrink-0 bg-stone-50 flex items-center justify-center">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ urlencode($stylist->slug) }}" alt="{{ $stylist->name }}" class="h-full w-full object-cover">
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-bold text-stone-900 text-xs uppercase tracking-tight">{{ $stylist->name }}</h4>
                                <span class="text-[9px] text-[#0A3D91] uppercase font-extrabold tracking-wider block mt-0.5">{{ $stylist->specialization }}</span>
                                <p class="text-stone-450 italic text-[10px] leading-relaxed mt-2">
                                    @if($stylist->name === 'Raka')
                                        "I like cuts that look effortless but still feel intentional."
                                    @else
                                        "Every service is a collaboration to find a style that expresses who you are."
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($currentStep === 3)
                <!-- Step 3: Choose Date & Time -->
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
                                   wire:model.live="selectedDate" 
                                   min="{{ \Carbon\Carbon::today()->toDateString() }}" />
                        </div>
                    </div>
                               <!-- Right Column: Stylists Availability (KAI Style) -->
                    <div class="lg:col-span-2 space-y-6">
                        @php
                            $selectedStylistSlots = $stylistSlots[$selectedStylistId] ?? [];
                        @endphp
                        
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">
                            {{ $isId ? 'JADWAL SESI & KETERSEDIAAN STYLIST PADA TANGGAL INI' : 'SESSION SCHEDULE & STYLIST AVAILABILITY FOR THIS DATE' }}
                        </label>
                        
                        <!-- Preferred Stylist Card -->
                        <div class="border rounded-2xl p-6 bg-white {{ !empty($selectedStylistSlots) ? 'border-[#0A3D91] ring-1 ring-[#0A3D91]/10 shadow-sm' : 'border-stone-200' }} flex flex-col gap-6">
                            <!-- Top Part: Stylist Profile -->
                            <div class="flex items-center space-x-4">
                                <div class="h-12 w-12 rounded-lg overflow-hidden border border-stone-200 shadow-inner flex-shrink-0 bg-stone-50 flex items-center justify-center">
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ urlencode($selectedStylist->slug) }}" alt="{{ $selectedStylist->name }}" class="h-full w-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h4 class="font-bold text-stone-900 text-xs uppercase tracking-tight">{{ $selectedStylist->name }}</h4>
                                        <span class="text-[8px] uppercase tracking-wider bg-blue-50 text-[#0A3D91] px-2 py-0.5 rounded font-extrabold">{{ $isId ? 'Pilihan Utama' : 'Preferred' }}</span>
                                    </div>
                                    <span class="text-[9px] text-stone-400 uppercase font-extrabold tracking-wider block mt-0.5">{{ $selectedStylist->specialization }}</span>
                                </div>
                            </div>

                            <!-- Bottom Part: Slots Grid -->
                            <div class="pt-4 border-t border-stone-100">
                                @if(empty($selectedStylistSlots))
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[9px] uppercase tracking-widest font-extrabold bg-red-50 border border-red-100 text-red-500 select-none">
                                        {{ $isId ? 'Habis / Sold Out' : 'Fully Booked' }}
                                    </span>
                                @else
                                    <label class="block text-[9px] uppercase tracking-widest text-stone-400 font-extrabold mb-3">
                                        {{ $isId ? 'Pilih Jam Sesi Yang Tersedia' : 'Select Available Session Slot' }}
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($selectedStylistSlots as $slot)
                                            <button type="button" 
                                                    class="py-2.5 px-4 min-w-[75px] text-center rounded-lg border text-xs font-bold font-mono transition {{ $selectedTime == $slot['time'] ? 'border-[#0A3D91] bg-blue-50/20 text-[#0A3D91]' : 'border-stone-200 text-stone-700 hover:border-[#0A3D91]' }}"
                                                    wire:click="selectTime('{{ $slot['time'] }}')">
                                                {{ $slot['time'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Alternative Stylist Suggestion (only shown if preferred stylist is sold out) -->
                        @if(empty($selectedStylistSlots))
                            <div class="pt-6 border-t border-stone-200 space-y-4">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-bold uppercase tracking-tight text-stone-905">
                                        {{ $isId ? 'Rekomendasi Alternatif Stylist Lain Yang Tersedia' : 'Recommended Available Alternative Stylists' }}
                                    </span>
                                </div>
                                
                                @php
                                    $hasAlternatives = false;
                                @endphp
                                
                                <div class="space-y-4">
                                    @foreach($stylists as $stylistItem)
                                        @if($stylistItem->id != $selectedStylistId)
                                            @php
                                                $itemSlots = $stylistSlots[$stylistItem->id] ?? [];
                                            @endphp
                                            
                                            @if(!empty($itemSlots))
                                                @php
                                                    $hasAlternatives = true;
                                                @endphp
                                                <div class="border border-stone-200 rounded-2xl p-6 bg-white hover:shadow-sm transition flex flex-col gap-6">
                                                    <!-- Top Part: Stylist Profile -->
                                                    <div class="flex items-center space-x-4">
                                                        <div class="h-12 w-12 rounded-lg bg-stone-55 text-stone-455 font-extrabold text-sm select-none flex items-center justify-center border border-stone-200">
                                                            {{ collect(explode(' ', $stylistItem->name))->map(fn($n) => substr($n, 0, 1))->join('') }}
                                                        </div>
                                                        <div>
                                                            <h4 class="font-bold text-stone-900 text-xs uppercase tracking-tight">{{ $stylistItem->name }}</h4>
                                                            <span class="text-[9px] text-stone-400 uppercase font-extrabold tracking-wider block mt-0.5">{{ $stylistItem->specialization }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Bottom Part: Slots Grid -->
                                                    <div class="pt-4 border-t border-stone-100">
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($itemSlots as $slot)
                                                                <button type="button" 
                                                                        class="py-2.5 px-4 min-w-[75px] rounded-lg border bg-white text-xs font-bold font-mono transition text-center border-stone-200 text-stone-700 hover:border-[#0A3D91]"
                                                                        wire:click="selectStylistAndSlot({{ $stylistItem->id }}, '{{ $slot['time'] }}')">
                                                                    {{ $slot['time'] }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach
                                    
                                    @if(!$hasAlternatives)
                                        <p class="text-stone-400 text-xs py-6 bg-stone-50 text-center rounded-xl border select-none">
                                            {{ $isId ? 'Maaf, semua stylist penuh atau tidak tersedia pada tanggal ini.' : 'Sorry, all stylists are fully booked on this date.' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>    </div>
                </div>

            @elseif($currentStep === 4)
                <!-- Step 4: Confirm -->
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
                        
                        @if(session()->has('autoFillSuccess'))
                            <div class="mb-4">
                                <x-ui.alert variant="info" title="Profil Ditemukan">
                                    {{ session('autoFillSuccess') }}
                                </x-ui.alert>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input wire:key="field-phone" label="Nomor WhatsApp" placeholder="e.g. 081234567890" wire:model.live.debounce.300ms="phone" :error="$errors->first('phone')" />
                            <x-ui.input wire:key="field-name" label="{{ $isId ? 'Nama Lengkap' : 'Full Name' }}" placeholder="John Doe" wire:model.defer="customerName" :error="$errors->first('customerName')" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-ui.input wire:key="field-email" label="Email" type="email" placeholder="john@example.com" wire:model.defer="email" :error="$errors->first('email')" />
                            <x-ui.input wire:key="field-birthdate" label="{{ $isId ? 'Tanggal Lahir' : 'Birth Date' }}" type="date" wire:model.defer="birthDate" :error="$errors->first('birthDate')" />
                            <x-ui.select wire:key="field-gender" label="Gender" wire:model.defer="gender" :error="$errors->first('gender')">
                                <option value="">Pilih Gender</option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </x-ui.select>
                        </div>
                    </div>

                    <!-- Promo Code -->
                    <div class="border border-stone-200 rounded-xl p-6 bg-white space-y-4">
                        <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-stone-400 border-b pb-2">02. Kode Promo / Voucher</h3>
                        <div class="flex space-x-3 items-end">
                            <div class="flex-grow">
                                <x-ui.input wire:key="field-promo" placeholder="e.g. WELCOME50" wire:model.live.debounce.300ms="promoCode" />
                            </div>
                            <x-ui.button variant="outline" wire:click="applyPromo" class="h-[46px] rounded-lg">Gunakan</x-ui.button>
                        </div>
                        @if($promoError) <x-ui.alert variant="danger">{{ $promoError }}</x-ui.alert> @endif
                        @if($promoSuccess) <x-ui.alert variant="success">{{ $promoSuccess }}</x-ui.alert> @endif
                    </div>

                    @php
                        $isGatewayActive = \App\Domains\CMS\Services\CmsService::get('payment_gateway_active') === 'true';
                    @endphp

                    @if($isGatewayActive)
                        <!-- Payment Selector -->
                        <div class="border border-stone-200 rounded-xl p-6 bg-white space-y-4">
                            <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-stone-400 border-b pb-2">03. Metode Pembayaran</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border rounded-lg p-4 cursor-pointer text-center {{ $paymentMethod === 'manual' ? 'border-[#0A3D91] bg-blue-50/20' : 'border-stone-200' }}"
                                     wire:click="selectPayment('manual')">
                                    <span class="text-xs font-bold block text-stone-850 uppercase">Bayar di Outlet</span>
                                </div>
                                <div class="border rounded-lg p-4 cursor-pointer text-center {{ $paymentMethod === 'midtrans' ? 'border-[#0A3D91] bg-blue-50/20' : 'border-stone-200' }}"
                                     wire:click="selectPayment('midtrans')">
                                    <span class="text-xs font-bold block text-stone-850 uppercase">Bayar Sekarang (Online)</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="pt-4 flex justify-end">
                        <x-ui.button variant="primary" size="lg" wire:click="confirmBooking">
                            Book your experience
                        </x-ui.button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar Summary Cards (Right Column) -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Selected Outlet Details Card -->
        <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm text-center">
            <div class="mb-4">
                <img src="/logo/logo.png" alt="MORE" class="h-10 mx-auto object-contain">
            </div>
            
            <h3 class="text-base font-bold font-sans text-stone-900 mt-2 uppercase tracking-wider">
                {{ $selectedOutlet ? $selectedOutlet->name : 'More Hair Studio' }}
            </h3>

            <!-- Rating summary -->
            <div class="flex items-center justify-center space-x-2 mt-2 text-xxs text-stone-400 font-extrabold uppercase tracking-widest">
                <span class="text-stone-850">Rating: 5.0</span>
                <span>•</span>
                <span>229 reviews</span>
            </div>

            <p class="text-stone-500 text-xxs leading-relaxed mt-4 border-t border-stone-100 pt-4 px-2 font-light">
                {{ $selectedOutlet ? $selectedOutlet->address : 'Pilih outlet terdekat untuk memuat informasi alamat lengkap.' }}
            </p>
        </div>

        <!-- Ringkasan Pemesanan Card -->
        <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h4 class="font-extrabold text-[10px] uppercase tracking-wider text-stone-400 border-b border-stone-100 pb-2">
                {{ $isId ? 'Ringkasan Pemesanan' : 'Booking Summary' }}
            </h4>

            <div class="text-xs space-y-2.5 font-medium text-stone-600">
                <div class="flex justify-between">
                    <span>Selected Services:</span>
                    <span class="text-stone-850 font-bold font-mono">
                        {{ $selectedServiceId ? 1 : 0 }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Est. Duration:</span>
                    <span class="text-stone-850 font-bold font-mono">
                        {{ $selectedServiceId ? $serviceDuration : 0 }} {{ $isId ? 'Menit' : 'Min' }}
                    </span>
                </div>
                @if($selectedStylist)
                    <div class="flex justify-between">
                        <span>Stylist:</span>
                        <span class="text-stone-850 font-bold uppercase">
                            {{ $selectedStylist->name }}
                        </span>
                    </div>
                @endif
                @if($selectedTime)
                    <div class="flex justify-between">
                        <span>Session Time:</span>
                        <span class="text-stone-850 font-bold font-mono">
                            {{ $selectedTime }}
                        </span>
                    </div>
                @endif
                @if($discountAmount > 0)
                    <div class="flex justify-between border-t border-stone-100 pt-2 text-stone-450">
                        <span>{{ $isId ? 'Harga Asli' : 'Original Price' }}:</span>
                        <span class="font-mono text-stone-700 line-through">
                            Rp {{ number_format($servicePrice, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between text-emerald-600 font-extrabold">
                        <span>{{ $isId ? 'Diskon Promo' : 'Promo Discount' }}:</span>
                        <span class="font-mono">
                            -Rp {{ number_format($discountAmount, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="border-t border-stone-100 pt-3 flex justify-between items-center text-xs font-extrabold text-stone-900 uppercase">
                <span>{{ $isId ? 'Total Bayar' : 'Total Price' }}:</span>
                <span class="text-[#0A3D91] font-mono text-sm font-black">
                    Rp {{ number_format(max(0, $servicePrice - $discountAmount), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>
