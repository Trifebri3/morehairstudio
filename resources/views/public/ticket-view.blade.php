<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Digital - More Hair Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white border border-stone-200 rounded-3xl p-6 shadow-xl space-y-6">
        <!-- Logo Header -->
        <div class="text-center pb-4 border-b">
            <h1 class="text-lg font-black tracking-widest text-[#0A3D91] uppercase">MORE HAIR STUDIO</h1>
            <p class="text-[9px] font-bold text-stone-400 uppercase tracking-widest mt-1">E-Ticket & Passcode Verification</p>
        </div>

        <!-- Ticket Core Info -->
        <div class="space-y-4">
            <div class="flex justify-between items-center text-xs font-bold text-stone-500">
                <span>Pelanggan</span>
                <span class="text-stone-900">{{ $booking->customer->name }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-bold text-stone-500">
                <span>Kode Reservasi</span>
                <span class="font-mono text-stone-900">{{ $booking->booking_code }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-bold text-stone-500">
                <span>Status Kunjungan</span>
                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-50 border border-blue-100 text-[#0A3D91]">
                    {{ $booking->status }}
                </span>
            </div>
            <div class="flex justify-between items-center text-xs font-bold text-stone-500">
                <span>Studio Outlet</span>
                <span class="text-stone-900">{{ $booking->outlet->name }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-bold text-stone-500">
                <span>Jadwal Sesi</span>
                <span class="text-stone-900">{{ $booking->booking_date->format('d M Y') }}</span>
            </div>
            @if($booking->stylist)
                <div class="flex justify-between items-center text-xs font-bold text-stone-500">
                    <span>Stylist / Barber</span>
                    <span class="text-stone-900">{{ $booking->stylist->name }}</span>
                </div>
            @endif
        </div>

        <!-- Services -->
        <div class="border-t pt-4 space-y-2">
            <span class="block text-[10px] font-black uppercase text-stone-400 tracking-wider">Layanan Terpilih</span>
            <div class="divide-y text-xs font-bold text-stone-700">
                @foreach($booking->items as $item)
                    <div class="py-2 flex justify-between">
                        <span>{{ $item->service->name }}</span>
                        <span class="font-mono text-stone-500">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Verification QR & Passcode -->
        <div class="border-t pt-6 text-center space-y-4 bg-stone-50/50 p-4 rounded-2xl">
            <span class="block text-[10px] font-black uppercase text-stone-400 tracking-wider">Passcode Tiket Resmi</span>
            <div class="inline-block px-6 py-2 bg-white border font-mono text-lg font-black tracking-widest text-[#0A3D91] rounded-xl shadow-sm">
                {{ $ticket->passcode }}
            </div>
            <p class="text-[9px] text-stone-450 leading-relaxed max-w-xs mx-auto">
                Tunjukkan halaman ini atau kode Passcode di atas pada kasir/barber saat kedatangan di outlet untuk memverifikasi kedatangan Anda.
            </p>
        </div>

        <!-- Footer -->
        <div class="text-center text-[9px] text-stone-400 pt-2">
            More Hair Studio &copy; {{ date('Y') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
