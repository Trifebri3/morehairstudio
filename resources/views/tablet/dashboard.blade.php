@extends('layouts.tablet')

@section('content')
<div class="flex flex-col justify-center items-center py-4 sm:py-8 w-full">

    <!-- Top Terminal Title & Quick Status -->
    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10 space-y-3">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-stone-200/60 text-stone-700 text-[10px] sm:text-xs font-mono font-bold tracking-wider uppercase">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Terminal Operasional Kiosk &bull; {{ $outlet->name ?? 'MORE Hair Studio' }}</span>
        </div>

        <h1 class="text-2xl sm:text-4xl font-black text-stone-950 tracking-tight uppercase">
            Studio Operation <span class="text-[#c9512d]">Terminal</span>
        </h1>
        <p class="text-xs sm:text-sm text-stone-500 font-medium">
            Pilih modul operasional untuk melayani customer, verifikasi kedatangan, atau mengelola antrean studio.
        </p>

        <!-- Live Metrics Pills -->
        <div class="pt-2 flex items-center justify-center gap-2 sm:gap-3 flex-wrap font-mono text-xs">
            <div class="px-3 py-1.5 rounded-xl bg-white border border-stone-200 shadow-2xs flex items-center space-x-2">
                <span class="text-stone-400 text-[10px] uppercase font-bold">Antrean Aktif:</span>
                <strong class="text-stone-900 font-extrabold text-sm">{{ $activeQueueCount ?? 0 }}</strong>
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-white border border-stone-200 shadow-2xs flex items-center space-x-2">
                <span class="text-stone-400 text-[10px] uppercase font-bold">Stylist Siap:</span>
                <strong class="text-[#c9512d] font-extrabold text-sm">{{ $stylistsCount ?? 3 }}</strong>
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-white border border-stone-200 shadow-2xs flex items-center space-x-2">
                <span class="text-stone-400 text-[10px] uppercase font-bold">Selesai Hari Ini:</span>
                <strong class="text-emerald-600 font-extrabold text-sm">{{ $completedTodayCount ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <!-- 5 Large Touch Tiles for Tablet / iPad -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 w-full max-w-5xl">

        <!-- 1. Walk-In Booking -->
        <a href="{{ route('tablet.walk-in') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-[#faede7] text-[#c9512d] flex items-center justify-center shadow-xs group-hover:bg-[#c9512d] group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <span class="text-[9px] uppercase tracking-wider bg-[#faede7] text-[#c9512d] font-extrabold px-2.5 py-1 rounded-full">
                        Pelanggan Datang
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="text-lg font-bold text-stone-900 tracking-tight group-hover:text-[#c9512d] transition">
                        Walk-In Booking
                    </h2>
                    <p class="text-stone-500 text-xs leading-relaxed font-medium">
                        Mulai reservasi instan di tempat untuk customer langsung tanpa antrean lama.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-[#c9512d]">
                <span>Buka Form Walk-In</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 2. Check-In Scanner -->
        <a href="{{ route('tablet.check-in') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs group-hover:bg-emerald-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <span class="text-[9px] uppercase tracking-wider bg-emerald-50 text-emerald-700 font-extrabold px-2.5 py-1 rounded-full">
                        Kamera QR Siap
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="text-lg font-bold text-stone-900 tracking-tight group-hover:text-emerald-600 transition">
                        Scan &amp; Check-In
                    </h2>
                    <p class="text-stone-500 text-xs leading-relaxed font-medium">
                        Scan QR Code tiket booking customer atau input kode manual untuk verifikasi kedatangan.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-emerald-600">
                <span>Buka Scanner Kamera</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 3. Visual Queue -->
        <a href="{{ route('tablet.queue') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shadow-xs group-hover:bg-sky-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <span class="text-[9px] uppercase tracking-wider bg-sky-50 text-sky-700 font-extrabold px-2.5 py-1 rounded-full">
                        Live Monitor
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="text-lg font-bold text-stone-900 tracking-tight group-hover:text-sky-600 transition">
                        Visual Queue
                    </h2>
                    <p class="text-stone-500 text-xs leading-relaxed font-medium">
                        Pantau antrean aktif, alokasi kursi stylist, dan progres pengerjaan salon secara real-time.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-sky-600">
                <span>Pantau Antrean Live</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 4. Cashier Monitor (Styscreen) -->
        <a href="{{ route('tablet.styscreen') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shadow-xs group-hover:bg-amber-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-[9px] uppercase tracking-wider bg-amber-50 text-amber-800 font-extrabold px-2.5 py-1 rounded-full">
                        POS &amp; Kasir
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="text-lg font-bold text-stone-900 tracking-tight group-hover:text-amber-700 transition">
                        Cashier Monitor (Styscreen)
                    </h2>
                    <p class="text-stone-500 text-xs leading-relaxed font-medium">
                        Monitor transaksi kasir, proses pembayaran EDC / Tunai / QRIS, dan cetak invoice resi.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-amber-700">
                <span>Buka Layar Kasir</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 5. Stylist Attendance -->
        <a href="{{ route('tablet.attendance') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center shadow-xs group-hover:bg-purple-700 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-[9px] uppercase tracking-wider bg-purple-50 text-purple-800 font-extrabold px-2.5 py-1 rounded-full">
                        Presensi Harian
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="text-lg font-bold text-stone-900 tracking-tight group-hover:text-purple-700 transition">
                        Stylist Attendance
                    </h2>
                    <p class="text-stone-500 text-xs leading-relaxed font-medium">
                        Presensi masuk dan pulang (Clock In / Clock Out) hair artist bertugas hari ini.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-purple-700">
                <span>Kelola Absensi Artis</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

    </div>

</div>
@endsection
