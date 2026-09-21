@extends('layouts.tablet')

@section('content')
<style>
/* ── TABLET DASHBOARD FONTS ── */
.tb-stat-lbl { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 700; }
.tb-stat-val { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; font-size: 0.9rem; }
.tb-badge    { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.55rem; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 700; }
.tb-h2       { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; letter-spacing: -0.01em; }
.tb-desc     { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; line-height: 1.6; }
.tb-action   { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; }
</style>

<div class="flex flex-col justify-center items-center py-4 sm:py-8 w-full">

    <!-- Top Terminal Title & Quick Status -->
    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10 space-y-3">
        <!-- Live Metrics Pills -->
        <div class="pt-2 flex items-center justify-center gap-2 sm:gap-3 flex-wrap">
            <div class="px-3 py-1.5 rounded-xl bg-white border border-stone-200 shadow-sm flex items-center space-x-2">
                <span class="tb-stat-lbl text-stone-400">Antrean Aktif:</span>
                <strong class="tb-stat-val text-stone-900">{{ $activeQueueCount ?? 0 }}</strong>
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-white border border-stone-200 shadow-sm flex items-center space-x-2">
                <span class="tb-stat-lbl text-stone-400">Stylist Siap:</span>
                <strong class="tb-stat-val text-[#c9512d]">{{ $stylistsCount ?? 3 }}</strong>
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-white border border-stone-200 shadow-sm flex items-center space-x-2">
                <span class="tb-stat-lbl text-stone-400">Selesai Hari Ini:</span>
                <strong class="tb-stat-val text-emerald-600">{{ $completedTodayCount ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <!-- 5 Large Touch Tiles for Tablet / iPad -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 w-full max-w-5xl">

        <!-- 1. Walk-In Booking -->
        <a href="{{ route('tablet.walk-in') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden bg-white border border-stone-200 shadow-sm hover:border-[#c9512d]/40 transition duration-200">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-[#faede7] text-[#c9512d] flex items-center justify-center shadow-sm group-hover:bg-[#c9512d] group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <span class="tb-badge bg-[#faede7] text-[#c9512d] px-2.5 py-1 rounded-full">
                        Pelanggan Datang
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="tb-h2 text-lg text-stone-900 group-hover:text-[#c9512d] transition">
                        Walk-In Booking
                    </h2>
                    <p class="tb-desc text-stone-500 text-xs">
                        Mulai reservasi instan di tempat untuk customer langsung tanpa antrean lama.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs text-[#c9512d]">
                <span class="tb-action">Buka Form Walk-In</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 2. Check-In Scanner -->
        <a href="{{ route('tablet.check-in') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden bg-white border border-stone-200 shadow-sm hover:border-emerald-600/40 transition duration-200">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm group-hover:bg-emerald-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <span class="tb-badge bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">
                        Kamera QR Siap
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="tb-h2 text-lg text-stone-900 group-hover:text-emerald-600 transition">
                        Scan &amp; Check-In
                    </h2>
                    <p class="tb-desc text-stone-500 text-xs">
                        Scan QR Code tiket booking customer atau input kode manual untuk verifikasi kedatangan.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs text-emerald-600">
                <span class="tb-action">Buka Scanner Kamera</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 3. Visual Queue -->
        <a href="{{ route('tablet.queue') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden bg-white border border-stone-200 shadow-sm hover:border-sky-600/40 transition duration-200">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shadow-sm group-hover:bg-sky-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <span class="tb-badge bg-sky-50 text-sky-700 px-2.5 py-1 rounded-full">
                        Live Monitor
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="tb-h2 text-lg text-stone-900 group-hover:text-sky-600 transition">
                        Visual Queue
                    </h2>
                    <p class="tb-desc text-stone-500 text-xs">
                        Pantau antrean aktif, alokasi kursi stylist, dan progres pengerjaan salon secara real-time.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs text-sky-600">
                <span class="tb-action">Pantau Antrean Live</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 4. Cashier Monitor (Styscreen) -->
        <a href="{{ route('tablet.styscreen') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden bg-white border border-stone-200 shadow-sm hover:border-amber-600/40 transition duration-200">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shadow-sm group-hover:bg-amber-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="tb-badge bg-amber-50 text-amber-800 px-2.5 py-1 rounded-full">
                        POS &amp; Kasir
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="tb-h2 text-lg text-stone-900 group-hover:text-amber-700 transition">
                        Cashier Monitor (Styscreen)
                    </h2>
                    <p class="tb-desc text-stone-500 text-xs">
                        Monitor transaksi kasir, proses pembayaran EDC / Tunai / QRIS, dan cetak invoice resi.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs text-amber-700">
                <span class="tb-action">Buka Layar Kasir</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 5. Stylist Attendance -->
        <a href="{{ route('tablet.attendance') }}" 
           class="tablet-card rounded-3xl p-6 sm:p-7 flex flex-col justify-between group active:scale-[0.98] cursor-pointer relative overflow-hidden bg-white border border-stone-200 shadow-sm hover:border-purple-700/40 transition duration-200">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center shadow-sm group-hover:bg-purple-700 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="tb-badge bg-purple-50 text-purple-800 px-2.5 py-1 rounded-full">
                        Presensi Harian
                    </span>
                </div>

                <div class="space-y-1.5">
                    <h2 class="tb-h2 text-lg text-stone-900 group-hover:text-purple-700 transition">
                        Stylist Attendance
                    </h2>
                    <p class="tb-desc text-stone-500 text-xs">
                        Presensi masuk dan pulang (Clock In / Clock Out) hair artist bertugas hari ini.
                    </p>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-stone-100 flex items-center justify-between text-xs text-purple-700">
                <span class="tb-action">Kelola Absensi Artis</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

    </div>

</div>
@endsection
