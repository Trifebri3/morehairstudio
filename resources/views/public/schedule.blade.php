@extends('layouts.public')

@section('title', 'Cek Kalender & Jadwal Stylist | MORE Hair Studio')
@section('meta_description', 'Periksa ketersediaan jam booking seluruh hairstylist MORE Hair Studio secara transparan per tanggal.')

@section('content')
@php
    $todayDate = \Carbon\Carbon::today()->toDateString();
    $tomorrowDate = \Carbon\Carbon::tomorrow()->toDateString();
    $dayAfterDate = \Carbon\Carbon::today()->addDays(2)->toDateString();
@endphp

<div class="bg-[#fafaf9] min-h-screen py-10 md:py-16 font-sans selection:bg-[#c9512d] selection:text-white"
     x-data="scheduleChecker()"
     x-init="init()">
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="max-w-2xl mx-auto text-center space-y-3 mb-10">
            <h1 class="text-2xl sm:text-4xl font-black text-stone-900 tracking-tight uppercase">
                Kalender &amp; <span class="text-[#c9512d]">Jadwal Stylist</span>
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 font-normal">
                Pilih tanggal untuk melihat ketersediaan jam hair artist kami. Jadwal disinkronkan langsung dengan data outlet dan reservasi aktif.
            </p>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Calendar & Outlet Filter (lg:col-span-4) -->
            <div class="lg:col-span-4 space-y-5">
                
                <!-- Outlet Selector Card -->
                <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-2xs space-y-2.5">
                    <label class="block text-[10px] font-mono uppercase tracking-widest text-[#c9512d] font-extrabold">
                        Lokasi Studio
                    </label>
                    <div class="relative">
                        <select x-model="selectedOutletId" 
                                @change="onOutletChange()"
                                class="w-full bg-[#fafaf9] border border-stone-200 rounded-xl py-2.5 px-3 text-xs font-bold text-stone-900 focus:outline-none focus:border-[#c9512d] appearance-none cursor-pointer">
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }} &bull; {{ explode(',', $outlet->address)[0] ?? 'Bandung' }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-stone-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Calendar Card -->
                <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-[#c9512d] font-extrabold">
                            Pilih Tanggal
                        </label>
                        <button type="button" @click="goToToday()" class="text-[10px] font-bold text-[#c9512d] hover:underline">
                            Hari Ini
                        </button>
                    </div>

                    <!-- Calendar Header (Month + Year + Nav) -->
                    <div class="flex items-center justify-between border-b border-stone-100 pb-2.5">
                        <button type="button" 
                                @click="prevMonth()" 
                                class="p-1 rounded-lg border border-stone-200 text-stone-600 hover:bg-stone-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <span class="text-xs font-black uppercase tracking-tight text-stone-900 font-mono" x-text="currentMonthLabel"></span>

                        <button type="button" 
                                @click="nextMonth()" 
                                class="p-1 rounded-lg border border-stone-200 text-stone-600 hover:bg-stone-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    <!-- Days header -->
                    <div class="grid grid-cols-7 gap-1 text-center">
                        <template x-for="dayName in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="dayName">
                            <div class="text-[9px] font-bold text-stone-400 uppercase py-0.5" x-text="dayName"></div>
                        </template>
                    </div>

                    <!-- Days Grid -->
                    <div class="grid grid-cols-7 gap-1">
                        <template x-for="blank in calendarDays.blanks" :key="'blank-' + blank">
                            <div class="h-8"></div>
                        </template>

                        <template x-for="day in calendarDays.days" :key="day.dateStr">
                            <button type="button"
                                    :disabled="day.isPast"
                                    @click="selectDate(day.dateStr)"
                                    class="h-8 rounded-lg flex items-center justify-center text-xs font-mono transition"
                                    :class="{
                                        'bg-[#c9512d] text-white font-black shadow-xs': selectedDate === day.dateStr,
                                        'text-stone-300 cursor-not-allowed': day.isPast,
                                        'text-stone-700 hover:bg-stone-100 cursor-pointer font-medium': !day.isPast && selectedDate !== day.dateStr,
                                        'ring-1 ring-stone-300 font-bold': day.isToday && selectedDate !== day.dateStr
                                    }">
                                <span x-text="day.dayNumber"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Quick buttons -->
                    <div class="pt-3 border-t border-stone-100 flex flex-wrap gap-1.5">
                        <button type="button" 
                                @click="selectDate('{{ $todayDate }}')"
                                class="py-1 px-2.5 rounded-lg text-[11px] font-mono font-bold transition"
                                :class="selectedDate === '{{ $todayDate }}' ? 'bg-[#c9512d] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'">
                            Hari Ini
                        </button>
                        <button type="button" 
                                @click="selectDate('{{ $tomorrowDate }}')"
                                class="py-1 px-2.5 rounded-lg text-[11px] font-mono font-bold transition"
                                :class="selectedDate === '{{ $tomorrowDate }}' ? 'bg-[#c9512d] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'">
                            Besok
                        </button>
                        <button type="button" 
                                @click="selectDate('{{ $dayAfterDate }}')"
                                class="py-1 px-2.5 rounded-lg text-[11px] font-mono font-bold transition"
                                :class="selectedDate === '{{ $dayAfterDate }}' ? 'bg-[#c9512d] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'">
                            Lusa
                        </button>
                    </div>

                </div>

            </div>

            <!-- Right Column: Stylist Schedule Board (lg:col-span-8) -->
            <div class="lg:col-span-8 space-y-4">
                
                <!-- Date Overview Header -->
                <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-[#c9512d] font-extrabold block">
                            Ketersediaan Tanggal
                        </span>
                        <h2 class="text-base sm:text-lg font-black text-stone-900 uppercase tracking-tight mt-0.5" x-text="formattedDateLabel"></h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="fetchData()" 
                                class="px-3 py-1.5 rounded-xl border border-stone-200 text-stone-600 hover:text-stone-900 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Refresh</span>
                        </button>

                        <a :href="'{{ route('booking.index') }}?outlet_id=' + selectedOutletId + '&date=' + selectedDate" 
                           class="px-4 py-1.5 rounded-xl bg-[#c9512d] text-white hover:bg-[#a03b1e] text-xs font-bold transition shadow-2xs">
                            Booking Tanggal Ini &rarr;
                        </a>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="bg-white border border-stone-200 rounded-2xl p-10 text-center space-y-2">
                    <div class="inline-block w-6 h-6 border-2 border-stone-200 border-t-[#c9512d] rounded-full animate-spin"></div>
                    <p class="text-xs text-stone-500 font-mono font-bold">Memuat ketersediaan stylist...</p>
                </div>

                <!-- Stylist Schedule Cards -->
                <div x-show="!loading" class="space-y-4">
                    <template x-for="stylist in stylists" :key="stylist.id">
                        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-2xs transition flex flex-col gap-4">
                            
                            <!-- Stylist Header -->
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center space-x-3.5">
                                    <div class="h-12 w-12 rounded-xl overflow-hidden border border-stone-200 bg-stone-50 flex items-center justify-center flex-shrink-0">
                                        <img :src="stylist.photo ? (stylist.photo.startsWith('http') || stylist.photo.startsWith('/') ? stylist.photo : '/storage/' + stylist.photo) : ('https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(stylist.slug))" 
                                             :alt="stylist.name" 
                                             class="h-full w-full object-cover">
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="font-bold text-stone-900 text-sm uppercase tracking-tight" x-text="stylist.name"></h3>
                                            <span class="text-[9px] uppercase tracking-wider bg-stone-100 text-stone-600 px-2 py-0.5 rounded font-extrabold" x-text="stylist.specialization"></span>
                                        </div>
                                        <span class="text-xs text-stone-400 font-mono block mt-0.5" x-text="getWorkingHoursText(stylist.id)"></span>
                                    </div>
                                </div>

                                <template x-if="isStylistWorking(stylist.id)">
                                    <a :href="'{{ route('booking.index') }}?outlet_id=' + selectedOutletId + '&stylist_id=' + stylist.id + '&date=' + selectedDate" 
                                       class="px-3.5 py-2 rounded-xl bg-stone-900 hover:bg-[#c9512d] text-white text-xs font-bold transition">
                                        Pilih Stylist &rarr;
                                    </a>
                                </template>
                                <template x-if="!isStylistWorking(stylist.id)">
                                    <span class="text-xs font-bold text-stone-400">Libur</span>
                                </template>
                            </div>

                            <!-- Occupied Slots Breakdown (Only real bookings from DB) -->
                            <div class="pt-3 border-t border-stone-100 text-xs">
                                <template x-if="!isStylistWorking(stylist.id)">
                                    <span class="text-stone-400">Stylist tidak bertugas pada tanggal ini.</span>
                                </template>

                                <template x-if="isStylistWorking(stylist.id) && getBookedIntervals(stylist.id).length > 0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-stone-400 font-medium text-[11px]">Sesi Terisi:</span>
                                        <template x-for="(b, idx) in getBookedIntervals(stylist.id)" :key="idx">
                                            <span class="px-2.5 py-1 rounded-md bg-stone-100 text-stone-800 font-mono text-[11px] font-bold"
                                                  x-text="b.start + ' - ' + b.end + ' WIB'"></span>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="isStylistWorking(stylist.id) && getBookedIntervals(stylist.id).length === 0">
                                    <span class="text-stone-400 font-medium">Semua jam operasional tersedia (belum ada booking).</span>
                                </template>
                            </div>

                        </div>
                    </template>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
function scheduleChecker() {
    return {
        selectedOutletId: {{ (int)$selectedOutletId }},
        selectedDate: '{{ $selectedDate }}',
        outlets: @json($outlets),
        stylists: @json($stylists),
        services: @json($services),
        
        loading: false,
        slots: {},
        busyIntervals: {},
        freeWindows: {},
        workingHours: {},

        currentYear: {{ \Carbon\Carbon::parse($selectedDate)->year }},
        currentMonth: {{ \Carbon\Carbon::parse($selectedDate)->month }},

        init() {
            this.fetchData();
        },

        get currentMonthLabel() {
            const date = new Date(this.currentYear, this.currentMonth - 1, 1);
            return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        },

        get formattedDateLabel() {
            try {
                const parts = this.selectedDate.split('-');
                const d = new Date(parts[0], parts[1] - 1, parts[2]);
                return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            } catch (e) {
                return this.selectedDate;
            }
        },

        get calendarDays() {
            const year = this.currentYear;
            const month = this.currentMonth - 1;
            
            const firstDayIndex = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();
            
            const todayStr = '{{ $todayDate }}';
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

        prevMonth() {
            if (this.currentMonth === 1) {
                this.currentMonth = 12;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },

        nextMonth() {
            if (this.currentMonth === 12) {
                this.currentMonth = 1;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },

        goToToday() {
            const today = new Date();
            this.currentYear = today.getFullYear();
            this.currentMonth = today.getMonth() + 1;
            this.selectedDate = '{{ $todayDate }}';
            this.fetchData();
        },

        selectDate(dateStr) {
            this.selectedDate = dateStr;
            const parts = dateStr.split('-');
            this.currentYear = parseInt(parts[0]);
            this.currentMonth = parseInt(parts[1]);
            this.fetchData();
        },

        onOutletChange() {
            this.fetchData();
        },

        async fetchData() {
            this.loading = true;
            try {
                const res = await fetch(`{{ route('booking.slots') }}?outlet_id=${this.selectedOutletId}&date=${this.selectedDate}`);
                const data = await res.json();
                
                this.slots = data.slots || {};
                this.busyIntervals = data.busy_intervals || {};
                this.freeWindows = data.free_windows || {};
                this.workingHours = data.working_hours || {};
                if (data.stylists && Array.isArray(data.stylists)) {
                    this.stylists = data.stylists;
                }
            } catch (e) {
                console.error('Failed to load slots:', e);
            } finally {
                this.loading = false;
            }
        },

        isStylistWorking(stylistId) {
            const wh = this.workingHours[stylistId];
            return wh ? Boolean(wh.is_working) : false;
        },

        getWorkingHoursText(stylistId) {
            const wh = this.workingHours[stylistId];
            if (!wh || !wh.is_working) return 'Libur';
            return 'Jam Operasional: ' + wh.start_time + ' - ' + wh.end_time + ' WIB';
        },

        getBookedIntervals(stylistId) {
            return this.busyIntervals[stylistId] || [];
        }
    };
}
</script>
@endsection
