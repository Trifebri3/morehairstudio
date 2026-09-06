<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket Digital - More Hair Studio 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-primary { font-family: 'Syne', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center p-4 sm:p-6 text-stone-900">
    <div class="max-w-md w-full bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
        
        <!-- Logo Header -->
        <div class="text-center pb-5 border-b border-stone-200 space-y-1">
            <span class="text-[10px] font-mono uppercase tracking-widest text-[#c9512d] font-bold">Identity Human-Hair Centered Design</span>
            <h1 class="text-xl font-black tracking-tight text-stone-900 uppercase font-primary">MORE HAIR STUDIO</h1>
            <p class="text-[10px] font-mono text-stone-400 uppercase tracking-wider">Official E-Ticket &bull; Passcode Verification</p>
        </div>

        <!-- Ticket Core Info -->
        <div class="space-y-3.5 text-xs">
            <div class="flex justify-between items-center text-stone-500">
                <span>Nama Pelanggan</span>
                <span class="font-bold text-stone-900">{{ $booking->customer?->name ?? 'Tamu' }}</span>
            </div>
            <div class="flex justify-between items-center text-stone-500">
                <span>Kode Reservasi</span>
                <span class="font-mono font-bold text-[#c9512d] bg-[#faede7] px-2 py-0.5 rounded border border-[#c9512d]/25">{{ $booking->booking_code }}</span>
            </div>
            <div class="flex justify-between items-center text-stone-500">
                <span>Status Sesi</span>
                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-stone-100 border border-stone-200 text-stone-800 font-mono">
                    {{ $booking->status }}
                </span>
            </div>
            <div class="flex justify-between items-center text-stone-500">
                <span>Studio Lounge</span>
                <span class="font-bold text-stone-900">{{ $booking->outlet?->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between items-center text-stone-500">
                <span>Jadwal Sesi</span>
                <span class="font-mono font-bold text-stone-900">{{ $booking->booking_date->format('d M Y') }} &bull; {{ substr($booking->booking_time, 0, 5) }} WIB</span>
            </div>
            @if($booking->stylist)
                <div class="flex justify-between items-center text-stone-500">
                    <span>Hair Artist</span>
                    <span class="font-bold text-stone-900">{{ $booking->stylist->name }}</span>
                </div>
            @endif
        </div>

        <!-- Services -->
        <div class="border-t border-stone-200 pt-4 space-y-2">
            <span class="block text-[10px] font-mono font-bold uppercase text-stone-400 tracking-wider">Treatment Terpilih</span>
            <div class="divide-y divide-stone-100 text-xs font-medium text-stone-700">
                @foreach($booking->items as $item)
                    <div class="py-2 flex justify-between items-center">
                        <span>{{ $item->service->name }}</span>
                        <span class="font-mono text-stone-800 font-bold">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Verification Passcode -->
        <div class="border border-stone-200 p-5 rounded-2xl text-center space-y-3 bg-[#faede7]/30">
            <span class="block text-[10px] font-mono font-bold uppercase text-[#c9512d] tracking-wider">Passcode Check-In Tablet</span>
            <div class="inline-block px-6 py-2.5 bg-white border border-[#c9512d]/40 font-mono text-xl font-black tracking-widest text-[#c9512d] rounded-xl shadow-2xs">
                {{ $ticket->passcode }}
            </div>
            <p class="text-[10px] text-stone-500 leading-relaxed max-w-xs mx-auto font-light">
                Tunjukkan halaman ini atau masukkan 5 digit passcode di atas pada tablet kiosk studio saat tiba di lokasi.
            </p>
        </div>

        <!-- Footer -->
        <div class="text-center text-[10px] font-mono text-stone-400 pt-2 border-t border-stone-100 flex justify-between items-center">
            <span>Property of (M) MORE</span>
            <span>Edition 2026</span>
        </div>
    </div>
</body>
</html>
