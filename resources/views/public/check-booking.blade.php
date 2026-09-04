@extends('layouts.public')

@section('title', 'Cek Jadwal & Status Booking | MORE Hair Studio')
@section('meta_description', 'Lihat ketersediaan jam booking hair artist secara transparan per tanggal, atau cek status reservasi Anda (AKTIF, SUDAH CEKIN, atau TIDAK ADA) di MORE Hair Studio.')

@section('content')
@php
    $todayDate = \Carbon\Carbon::today()->toDateString();
    $tomorrowDate = \Carbon\Carbon::tomorrow()->toDateString();
    $dayAfterDate = \Carbon\Carbon::today()->addDays(2)->toDateString();
    
    $todayLabel = \Carbon\Carbon::today()->translatedFormat('l, d M');
    $tomorrowLabel = \Carbon\Carbon::tomorrow()->translatedFormat('l, d M');
    $dayAfterLabel = \Carbon\Carbon::today()->addDays(2)->translatedFormat('l, d M');

    $selectedOutlet = $outlets->firstWhere('id', $selectedOutletId) ?? $defaultOutlet;
    $outletAddress = $selectedOutlet?->address ?? 'Jl. Mangga No. 37A, Cihapit, Bandung';
    $outletWa = $selectedOutlet?->whatsapp ?? '6282298347730';
@endphp

<div class="bg-white min-h-screen py-10 md:py-16 font-sans selection:bg-[#c9512d] selection:text-white"
     x-data="publicBookingCheck()"
     x-init="init()">
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Top Header -->
        <div class="max-w-3xl mx-auto text-center space-y-4 mb-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#f8eee8] border border-[#c9512d]/20 text-[#c9512d] text-xs font-mono font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-[#c9512d] animate-pulse"></span>
                <span>Transparansi Jadwal &amp; Status Reservasi</span>
            </div>

            <h1 class="text-3xl sm:text-5xl font-black text-[#171615] tracking-tight uppercase font-headline leading-tight">
                Cek Jadwal Booking <br class="hidden sm:inline">
                <span class="text-[#c9512d] font-serif italic font-normal lowercase">&amp;</span> Status Kunjungan
            </h1>

            <p class="text-sm sm:text-base text-stone-600 leading-relaxed font-light">
                Periksa jam yang sudah dibooking pada tanggal pilihan Anda, atau cek apakah booking Anda sudah AKTIF / SUDAH CEKIN.
            </p>
        </div>

        <!-- Navigation Tabs: Cek Jadwal vs Cek Status Booking -->
        <div class="max-w-md mx-auto mb-10">
            <div class="bg-stone-100 p-1.5 rounded-2xl flex border border-stone-200">
                <button type="button"
                        @click="switchTab('slots')"
                        class="flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold uppercase tracking-wider transition duration-200 flex items-center justify-center gap-2"
                        :class="activeTab === 'slots' ? 'bg-white text-[#171615] shadow-sm font-black' : 'text-stone-500 hover:text-[#171615]'">
                    <svg class="w-4 h-4" :class="activeTab === 'slots' ? 'text-[#c9512d]' : 'text-stone-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Cek Jadwal Stylist</span>
                </button>

                <button type="button"
                        @click="switchTab('status')"
                        class="flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold uppercase tracking-wider transition duration-200 flex items-center justify-center gap-2"
                        :class="activeTab === 'status' ? 'bg-white text-[#171615] shadow-sm font-black' : 'text-stone-500 hover:text-[#171615]'">
                    <svg class="w-4 h-4" :class="activeTab === 'status' ? 'text-[#c9512d]' : 'text-stone-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span>Cek Status Booking</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: CEK JADWAL BOOKING PER TANGGAL (HANYA TAMPILKAN JAM-JAM TERISI) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'slots'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            
            <!-- Fitur Cek Dengan Tanggal & Outlet Filter -->
            <div class="bg-stone-50 border border-stone-200 rounded-3xl p-6 sm:p-8 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    
                    <!-- 1. Lokasi Outlet -->
                    <div class="space-y-2">
                        <label class="block text-xs font-mono uppercase tracking-widest text-stone-500 font-extrabold">
                            Pilih Lokasi Studio:
                        </label>
                        <div class="relative">
                            <select x-model="selectedOutletId" 
                                    @change="onOutletChange()"
                                    class="w-full bg-white border border-stone-300 rounded-2xl py-3.5 px-4 text-xs font-bold text-[#171615] focus:outline-none focus:border-[#c9512d] appearance-none cursor-pointer">
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }} &bull; {{ explode(',', $outlet->address)[0] ?? 'Bandung' }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-stone-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Fitur Cek Tanggal -->
                    <div class="space-y-2">
                        <label class="block text-xs font-mono uppercase tracking-widest text-stone-500 font-extrabold">
                            Pilih Tanggal Pengecekan:
                        </label>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="relative flex-grow">
                                <input type="date" 
                                       x-model="selectedDate"
                                       @change="fetchSlots()"
                                       min="{{ $todayDate }}"
                                       max="{{ \Carbon\Carbon::today()->addDays(30)->toDateString() }}"
                                       class="w-full bg-white border border-stone-300 rounded-2xl py-3 px-4 text-xs font-mono font-bold text-[#171615] focus:outline-none focus:border-[#c9512d] cursor-pointer">
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button type="button" 
                                        @click="setDate('{{ $todayDate }}')"
                                        class="px-3 py-2 rounded-xl text-[11px] font-mono font-bold uppercase transition"
                                        :class="selectedDate === '{{ $todayDate }}' ? 'bg-[#c9512d] text-white shadow-2xs' : 'bg-white text-stone-700 border border-stone-200 hover:border-stone-400'">
                                    Hari Ini
                                </button>
                                <button type="button" 
                                        @click="setDate('{{ $tomorrowDate }}')"
                                        class="px-3 py-2 rounded-xl text-[11px] font-mono font-bold uppercase transition"
                                        :class="selectedDate === '{{ $tomorrowDate }}' ? 'bg-[#c9512d] text-white shadow-2xs' : 'bg-white text-stone-700 border border-stone-200 hover:border-stone-400'">
                                    Besok
                                </button>
                                <button type="button" 
                                        @click="setDate('{{ $dayAfterDate }}')"
                                        class="px-3 py-2 rounded-xl text-[11px] font-mono font-bold uppercase transition"
                                        :class="selectedDate === '{{ $dayAfterDate }}' ? 'bg-[#c9512d] text-white shadow-2xs' : 'bg-white text-stone-700 border border-stone-200 hover:border-stone-400'">
                                    Lusa
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Banner Info Tanggal Aktif -->
            <div class="flex items-center justify-between pb-3 border-b border-stone-200">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-mono uppercase tracking-widest text-[#171615] font-black">
                        Jadwal Booking Tanggal: <span class="text-[#c9512d]" x-text="formattedDateLabel"></span>
                    </span>
                </div>
                <button type="button" @click="fetchSlots()" class="text-[#c9512d] font-bold text-xs hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" :class="loadingSlots ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Segarkan</span>
                </button>
            </div>

            <!-- Loading State -->
            <div x-show="loadingSlots" class="py-16 text-center space-y-3">
                <div class="inline-block w-8 h-8 border-2 border-stone-200 border-t-[#c9512d] rounded-full animate-spin"></div>
                <p class="text-xs font-mono uppercase tracking-wider text-stone-500 font-bold">Memuat jam booking stylist...</p>
            </div>

            <!-- Daftar Stylist & Jam Yang Sudah di-Booking -->
            <div x-show="!loadingSlots" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <template x-for="stylist in currentStylists" :key="stylist.id">
                    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-7 shadow-sm hover:border-stone-300 transition duration-300 flex flex-col justify-between space-y-6">
                        
                        <!-- Header Stylist -->
                        <div>
                            <div class="flex items-center gap-4 pb-4 border-b border-stone-100">
                                <div class="w-14 h-14 rounded-2xl bg-stone-100 overflow-hidden border border-stone-200 flex-shrink-0 relative flex items-center justify-center">
                                    <template x-if="stylist.photo">
                                        <img :src="stylist.photo.startsWith('http') || stylist.photo.startsWith('/') ? stylist.photo : '/storage/' + stylist.photo" 
                                             :alt="stylist.name" 
                                             x-on:error="$el.style.display='none'; $el.nextElementSibling.classList.remove('hidden')"
                                             class="w-full h-full object-cover">
                                    </template>
                                    <div :class="stylist.photo ? 'hidden' : ''" class="w-full h-full flex items-center justify-center font-bold text-stone-600 font-mono text-lg uppercase" x-text="stylist.name.charAt(0)"></div>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-black text-base text-[#171615] uppercase tracking-tight" x-text="stylist.name"></h3>
                                        <span class="text-[10px] font-mono px-2.5 py-0.5 rounded-full bg-stone-100 text-stone-600 font-bold uppercase tracking-wider" x-text="stylist.specialization || 'Hair Specialist'"></span>
                                    </div>
                                    <span class="text-xs text-stone-500 font-light block mt-0.5">Jam Kerja: 10:00 &ndash; 20:00 WIB</span>
                                </div>
                            </div>

                            <!-- Jam Yang Sedang Terisi Booking (Sesuai Permintaan User: Cukup Jam Saja) -->
                            <div class="pt-5 space-y-3">
                                <span class="text-xs font-mono uppercase tracking-widest text-[#171615] font-extrabold block">
                                    Jadwal Booking Terisi:
                                </span>

                                <!-- Jika ada jadwal terisi -->
                                <template x-if="booked[stylist.id] && booked[stylist.id].length > 0">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="bSlot in booked[stylist.id]" :key="bSlot.start + '-' + bSlot.end">
                                                <div class="px-3 py-2 rounded-xl text-xs font-mono font-bold bg-[#f8eee8] text-[#c9512d] border border-[#c9512d]/30 flex items-center gap-2 shadow-2xs">
                                                    <span class="w-2 h-2 rounded-full bg-[#c9512d]"></span>
                                                    <span>Ada Booking: <span x-text="bSlot.start + ' - ' + bSlot.end + ' WIB'"></span></span>
                                                </div>
                                            </template>
                                        </div>
                                        <p class="text-[11px] text-stone-500 font-light pt-1 flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Jam di luar daftar booking di atas masih kosong dan dapat Anda pesan.</span>
                                        </p>
                                    </div>
                                </template>

                                <!-- Jika belum ada booking sama sekali -->
                                <template x-if="!booked[stylist.id] || booked[stylist.id].length === 0">
                                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-1">
                                        <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>Belum Ada Booking (Semua Jam Kosong)</span>
                                        </div>
                                        <p class="text-[11px] text-emerald-700 font-light">
                                            Seluruh jam kerja (10:00 &ndash; 20:00 WIB) tersedia penuh untuk tanggal ini.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Tombol Langsung Booking Stylist Ini -->
                        <div class="pt-4 border-t border-stone-100 flex items-center justify-between">
                            <span class="text-[10px] font-mono text-stone-400">Pilih jam kosong favorit Anda</span>
                            <a :href="getBookingUrl(stylist.id, null)" 
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-[#171615] text-white hover:bg-[#c9512d] transition duration-300 shadow-sm">
                                <span>Booking Stylist Ini</span>
                                <span>&rarr;</span>
                            </a>
                        </div>

                    </div>
                </template>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: CEK STATUS BOOKING (CUKUP: AKTIF, SUDAH CEKIN, ATAU TIDAK ADA) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'status'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            
            <!-- Form Pencarian -->
            <div class="max-w-xl mx-auto bg-stone-50 border border-stone-200 rounded-3xl p-6 sm:p-8 shadow-sm">
                <form @submit.prevent="performSearch()" class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-mono uppercase tracking-widest text-stone-500 font-extrabold">
                            Masukkan Nomor WhatsApp atau Kode Booking:
                        </label>
                        <div class="relative flex items-center">
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Contoh: 08123456789 atau MOR-..."
                                   class="w-full bg-white border border-stone-300 rounded-2xl py-4 pl-4 pr-32 text-sm font-mono text-[#171615] focus:outline-none focus:border-[#c9512d] placeholder:text-stone-400 font-bold transition shadow-xs">
                            
                            <button type="submit"
                                    :disabled="searching || !searchQuery.trim()"
                                    class="absolute right-2 top-2 bottom-2 px-5 rounded-xl bg-[#c9512d] hover:bg-[#b04322] text-white text-xs font-bold uppercase tracking-widest transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="searching">
                                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                </template>
                                <span x-text="searching ? 'Mengecek...' : 'Cek Status'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- HASIL KONDISI: TIDAK ADA -->
            <div x-show="searchError" class="max-w-xl mx-auto bg-stone-50 border border-stone-300 rounded-3xl p-8 text-center space-y-4">
                <div class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-stone-200 text-stone-700 font-mono font-black text-sm uppercase tracking-wider">
                    TIDAK ADA
                </div>
                <h4 class="text-base font-bold text-[#171615]">Data Booking Tidak Ditemukan</h4>
                <p class="text-xs text-stone-500 font-light max-w-sm mx-auto">
                    Tidak ditemukan data reservasi yang sesuai dengan nomor atau kode tersebut. Pastikan nomor yang dimasukkan benar.
                </p>
                <div class="pt-2">
                    <a href="{{ route('booking.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-[#c9512d] text-white text-xs font-bold uppercase tracking-wider hover:bg-[#b04322] transition shadow-sm">
                        Buat Reservasi Baru &rarr;
                    </a>
                </div>
            </div>

            <!-- HASIL KONDISI: ADA DATA (TAMPILKAN STATUS: AKTIF, SUDAH CEKIN, ATAU SELESAI) -->
            <div x-show="searchResults && searchResults.length > 0" class="max-w-2xl mx-auto space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono uppercase tracking-widest font-black text-[#171615]">
                        Hasil Pengecekan Booking (<span x-text="searchResults.length"></span>)
                    </h3>
                    <button type="button" @click="searchResults = []; searchQuery = ''" class="text-xs text-stone-400 hover:text-[#c9512d] transition font-mono">
                        Bersihkan
                    </button>
                </div>

                <template x-for="item in searchResults" :key="item.id">
                    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6 hover:border-stone-300 transition duration-300">
                        
                        <!-- Header Status yang Menonjol (AKTIF / SUDAH CEKIN) -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-stone-100">
                            <div>
                                <span class="text-[10px] font-mono uppercase tracking-widest text-stone-400 font-bold block">
                                    Kode Reservasi
                                </span>
                                <span class="text-xl font-mono font-black text-[#171615] tracking-wider" x-text="item.booking_code"></span>
                            </div>

                            <div>
                                <!-- AKTIF -->
                                <template x-if="item.status === 'confirmed' || item.status === 'pending'">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-mono font-black uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>AKTIF</span>
                                    </span>
                                </template>

                                <!-- SUDAH CEKIN -->
                                <template x-if="item.status === 'checked_in' || item.status === 'in_service'">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-mono font-black uppercase tracking-wider bg-[#f8eee8] text-[#c9512d] border border-[#c9512d]/30 shadow-2xs">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#c9512d]"></span>
                                        <span>SUDAH CEKIN</span>
                                    </span>
                                </template>

                                <!-- SELESAI -->
                                <template x-if="item.status === 'completed'">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-mono font-black uppercase tracking-wider bg-stone-100 text-stone-700 border border-stone-200">
                                        <span>SELESAI</span>
                                    </span>
                                </template>

                                <!-- KEDALUWARSA / BATAL -->
                                <template x-if="item.status === 'cancelled' || item.status === 'expired'">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-mono font-black uppercase tracking-wider bg-stone-100 text-stone-500 border border-stone-200">
                                        <span>KEDALUWARSA / BATAL</span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Data Ringkas (Cukup Jam, Stylist, Outlet) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                            <div class="bg-stone-50 rounded-2xl p-4 border border-stone-100 space-y-1">
                                <span class="text-[10px] font-mono text-stone-400 uppercase tracking-widest font-bold block">Jadwal Sesi</span>
                                <p class="font-bold text-[#171615]" x-text="item.booking_date"></p>
                                <p class="font-mono font-bold text-[#c9512d] text-sm" x-text="item.time_slot"></p>
                            </div>

                            <div class="bg-stone-50 rounded-2xl p-4 border border-stone-100 space-y-1">
                                <span class="text-[10px] font-mono text-stone-400 uppercase tracking-widest font-bold block">Hair Stylist</span>
                                <p class="font-bold text-[#171615] text-sm" x-text="item.stylist_name"></p>
                                <p class="text-stone-500 text-[11px] font-light" x-text="item.stylist_specialization"></p>
                            </div>

                            <div class="bg-stone-50 rounded-2xl p-4 border border-stone-100 space-y-1">
                                <span class="text-[10px] font-mono text-stone-400 uppercase tracking-widest font-bold block">Studio Outlet</span>
                                <p class="font-bold text-[#171615]" x-text="item.outlet_name"></p>
                                <p class="text-stone-500 text-[10px] font-light" x-text="item.outlet_address"></p>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

        </div>

    </div>
</div>

<script>
function publicBookingCheck() {
    return {
        activeTab: '{{ $initialTab ?? "slots" }}',
        selectedOutletId: {{ (int)$selectedOutletId }},
        selectedDate: '{{ $initialDate ?? $todayDate }}',
        outlets: @json($outlets),
        services: @json($services),
        stylists: @json($stylists),
        slots: {},
        booked: {},
        loadingSlots: false,
        searchQuery: '',
        searching: false,
        searchError: null,
        searchResults: [],

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('tab')) {
                this.activeTab = urlParams.get('tab');
            }
            if (urlParams.has('date')) {
                this.selectedDate = urlParams.get('date');
            }
            if (urlParams.has('q')) {
                this.searchQuery = urlParams.get('q');
                this.performSearch();
            }
            this.fetchSlots();
        },

        get currentOutlet() {
            return this.outlets.find(o => o.id == this.selectedOutletId) || this.outlets[0];
        },

        get currentOutletAddress() {
            return this.currentOutlet ? this.currentOutlet.address : 'Jl. Mangga No. 37A, Cihapit, Bandung';
        },

        get currentStylists() {
            return this.stylists.filter(s => s.outlet_id == this.selectedOutletId);
        },

        get formattedDateLabel() {
            if (!this.selectedDate) return 'Hari Ini';
            try {
                const d = new Date(this.selectedDate + 'T00:00:00');
                return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            } catch (e) {
                return this.selectedDate;
            }
        },

        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },

        setDate(dateStr) {
            this.selectedDate = dateStr;
            this.fetchSlots();
        },

        onOutletChange() {
            this.fetchSlots();
        },

        getBookingUrl(stylistId, time) {
            const serviceId = this.services.length > 0 ? this.services[0].id : 1;
            let url = '{{ route("booking.index") }}?outlet_id=' + this.selectedOutletId +
                      '&service_id=' + serviceId +
                      '&stylist_id=' + stylistId +
                      '&date=' + this.selectedDate;
            if (time) {
                url += '&time=' + encodeURIComponent(time) + '&step=4';
            } else {
                url += '&step=3';
            }
            return url;
        },

        async fetchSlots() {
            this.loadingSlots = true;
            try {
                const res = await fetch(`{{ route('booking.slots') }}?outlet_id=${this.selectedOutletId}&date=${this.selectedDate}`);
                const data = await res.json();
                
                this.slots = data.slots || {};
                this.booked = data.booked || {};
                if (data.stylists && Array.isArray(data.stylists)) {
                    this.stylists = data.stylists;
                }
            } catch (e) {
                console.error('Failed to load slots:', e);
            } finally {
                this.loadingSlots = false;
            }
        },

        async performSearch() {
            const q = this.searchQuery ? this.searchQuery.trim() : '';
            if (!q || q.length < 3) return;

            this.searching = true;
            this.searchError = null;
            this.searchResults = [];

            try {
                const res = await fetch('{{ route("booking.check.status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ query: q })
                });

                const data = await res.json();
                if (data.success && data.bookings && data.bookings.length > 0) {
                    this.searchResults = data.bookings;
                } else {
                    this.searchError = data.message || 'Tidak ditemukan reservasi dengan data tersebut.';
                }
            } catch (e) {
                this.searchError = 'Terjadi kendala saat menghubungi server. Silakan coba kembali.';
            } finally {
                this.searching = false;
            }
        }
    };
}
</script>
@endsection
