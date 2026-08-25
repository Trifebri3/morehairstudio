@extends('layouts.tablet')

@section('content')
<div class="h-full flex flex-col justify-center items-center py-10 relative">
    <div class="text-center mb-14 relative z-10">
        <h2 class="text-4xl font-bold text-stone-900 tracking-wider mb-3">Studio Operation Terminal</h2>
        <p class="text-xxs uppercase tracking-widest text-stone-400 font-bold font-mono">Pilih Aksi Operasional Outlet Terdaftar</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 w-full max-w-6xl relative z-10">
        <!-- Walk In Booking -->
        <a href="{{ route('tablet.walk-in') }}" class="glass-panel p-8 rounded-3xl text-center flex flex-col items-center justify-center hover:border-blue-500/50 hover:bg-stone-50 transition-all duration-300 min-h-[240px] group">
            <h3 class="text-lg font-bold text-stone-900 mb-2 tracking-wide group-hover:text-blue-600 transition duration-300">Walk-In Booking</h3>
            <p class="text-stone-500 text-xs leading-relaxed max-w-xs font-medium">
                Mulai booking langsung di tempat untuk customer walk-in (antrean otomatis).
            </p>
        </a>

        <!-- Check In Scanner -->
        <a href="{{ route('tablet.check-in') }}" class="glass-panel p-8 rounded-3xl text-center flex flex-col items-center justify-center hover:border-blue-500/50 hover:bg-stone-50 transition-all duration-300 min-h-[240px] group">
            <h3 class="text-lg font-bold text-stone-900 mb-2 tracking-wide group-hover:text-blue-600 transition duration-300">Scan & Check-In</h3>
            <p class="text-stone-500 text-xs leading-relaxed max-w-xs font-medium">
                Scan QR Code customer or input kode booking manual untuk verifikasi kedatangan.
            </p>
        </a>

        <!-- Stylist Attendance -->
        <a href="{{ route('tablet.attendance') }}" class="glass-panel p-8 rounded-3xl text-center flex flex-col items-center justify-center hover:border-blue-500/50 hover:bg-stone-50 transition-all duration-300 min-h-[240px] group">
            <h3 class="text-lg font-bold text-stone-900 mb-2 tracking-wide group-hover:text-blue-600 transition duration-300">Stylist Attendance</h3>
            <p class="text-stone-500 text-xs leading-relaxed max-w-xs font-medium">
                Halaman absen (Clock In / Clock Out) bagi stylist aktif hari ini.
            </p>
        </a>

        <!-- Active Queue -->
        <a href="{{ route('tablet.queue') }}" class="glass-panel p-8 rounded-3xl text-center flex flex-col items-center justify-center hover:border-blue-500/50 hover:bg-stone-50 transition-all duration-300 min-h-[240px] group">
            <h3 class="text-lg font-bold text-stone-900 mb-2 tracking-wide group-hover:text-blue-600 transition duration-300">Visual Queue</h3>
            <p class="text-stone-500 text-xs leading-relaxed max-w-xs font-medium">
                Pantau antrean aktif, status pengerjaan, dan alokasi stylist secara real-time.
            </p>
        </a>

        <!-- Cashier Monitor (Styscreen) -->
        <a href="{{ route('tablet.styscreen') }}" class="glass-panel p-8 rounded-3xl text-center flex flex-col items-center justify-center hover:border-[#0A3D91]/50 hover:bg-stone-50 transition-all duration-300 min-h-[240px] group border-emerald-500/30">
            <h3 class="text-lg font-bold text-stone-900 mb-2 tracking-wide group-hover:text-[#0A3D91] transition duration-300">Cashier Monitor (Styscreen)</h3>
            <p class="text-stone-500 text-xs leading-relaxed max-w-xs font-medium">
                Monitor transaksi kasir, proses pembayaran EDC/Tunai, dan cetak invoice resi.
            </p>
        </a>
    </div>
</div>
@endsection
