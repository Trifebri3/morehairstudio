@extends('layouts.booking')

@section('title', 'Booking Confirmed • E-Ticket Pass | MORE Hair Studio')

@section('content')
    <div class="max-w-md mx-auto space-y-8 font-sans py-6">
        <!-- Success Status Box -->
        <div class="text-center space-y-3">
            <div class="h-16 w-16 bg-[#faede7] rounded-full flex items-center justify-center mx-auto border border-[#c9512d]/30 shadow-sm animate-bounce">
                <svg class="h-8 w-8 text-[#c9512d]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-2xl font-black text-stone-900 uppercase tracking-tight font-primary">Booking Confirmed</h1>
            <p class="text-stone-500 text-xs max-w-xs mx-auto leading-relaxed font-light font-secondary">
                Sesi perawatan Anda telah dijadwalkan. Tunjukkan Grooming Pass ini saat kedatangan.
            </p>
        </div>

        <!-- Premium Ticket Container -->
        <div class="relative bg-white border border-stone-200 rounded-3xl shadow-xs overflow-hidden">
            <!-- Top Half: Branding & Header -->
            <div class="p-8 bg-stone-900 text-white flex justify-between items-center relative overflow-hidden">
                <!-- Decorative circle overlay -->
                <div class="absolute -top-12 -left-12 h-32 w-32 bg-stone-800 rounded-full opacity-50"></div>
                
                <div class="relative z-10 space-y-1">
                    <span class="text-[9px] uppercase tracking-widest text-[#c9512d] font-bold font-mono block">Official Pass &bull; 2026</span>
                    <img src="/logo/logo-desc-white.png" alt="MORE Hair Studio" class="h-7 w-auto object-contain" style="height: 28px; width: auto;">
                </div>
                
                <div class="relative z-10 text-right space-y-1">
                    <span class="text-[9px] uppercase tracking-widest text-stone-400 font-bold block font-mono">Location</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider block font-mono text-white">{{ $booking->outlet->name }}</span>
                </div>
            </div>

            <!-- Middle: Ticket Notches & Dashed Line Separator -->
            <div class="relative h-6 bg-white">
                <!-- Left notch -->
                <div class="absolute -left-3 -top-3 h-6 w-6 rounded-full bg-[#fafaf9] border-r border-stone-200 z-10"></div>
                <!-- Right notch -->
                <div class="absolute -right-3 -top-3 h-6 w-6 rounded-full bg-[#fafaf9] border-l border-stone-200 z-10"></div>
                <!-- Dashed separator -->
                <div class="absolute top-0 left-4 right-4 h-0.5 border-t-2 border-dashed border-stone-200"></div>
            </div>

            <!-- Bottom Half: Ticket Body & Summary -->
            <div class="p-8 space-y-8 bg-white">
                
                <!-- Booking code badge -->
                <div class="bg-[#faede7]/40 border border-[#c9512d]/25 rounded-2xl p-5 text-center relative overflow-hidden">
                    <span class="text-[9px] uppercase tracking-widest text-[#c9512d] font-bold font-mono block mb-1">Grooming Pass Code</span>
                    <span class="text-2xl font-mono font-black text-[#c9512d] tracking-widest block">{{ $booking->booking_code }}</span>
                    <p class="text-[10px] text-stone-500 mt-2 font-light">Scan kode QR di bawah pada kiosk kiosk studio saat tiba</p>

                    <!-- Dynamic QR Code -->
                    <div class="mt-6 flex justify-center">
                        <div class="p-4 bg-white border border-[#c9512d]/30 rounded-2xl relative shadow-2xs">
                            <!-- Corner guides in Burnt Orange -->
                            <div class="absolute top-0 left-0 w-3 h-3 border-t-2 border-l-2 border-[#c9512d] rounded-tl-md"></div>
                            <div class="absolute top-0 right-0 w-3 h-3 border-t-2 border-r-2 border-[#c9512d] rounded-tr-md"></div>
                            <div class="absolute bottom-0 left-0 w-3 h-3 border-b-2 border-l-2 border-[#c9512d] rounded-bl-md"></div>
                            <div class="absolute bottom-0 right-0 w-3 h-3 border-b-2 border-r-2 border-[#c9512d] rounded-br-md"></div>
                            
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&color=c9512d&data={{ urlencode($booking->booking_code) }}" 
                                 alt="QR Code" 
                                 class="w-32 h-32 object-contain"
                                 style="width: 128px; height: 128px;">
                        </div>
                    </div>
                </div>

                <!-- Details list -->
                <div class="space-y-4 text-xs font-secondary">
                    <div class="flex justify-between items-center border-b border-stone-100 pb-3">
                        <span class="text-stone-400 font-bold uppercase tracking-wider text-[9px] font-mono">Treatment</span>
                        <span class="text-stone-900 font-bold text-right">{{ $booking->items->first()?->service?->name ?? 'Custom Service' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-stone-100 pb-3">
                        <span class="text-stone-400 font-bold uppercase tracking-wider text-[9px] font-mono">Hair Artist</span>
                        <span class="text-stone-900 font-bold text-right">{{ $booking->stylist?->name ?? 'Any Stylist' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-stone-100 pb-3">
                        <span class="text-stone-400 font-bold uppercase tracking-wider text-[9px] font-mono">Date</span>
                        <span class="text-stone-900 font-bold text-right">{{ $booking->booking_date->format('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-stone-100 pb-3">
                        <span class="text-stone-400 font-bold uppercase tracking-wider text-[9px] font-mono">Session Time</span>
                        <span class="text-stone-900 font-mono font-bold text-right">{{ substr($booking->items->first()->start_time, 0, 5) }} WIB</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-stone-100 pb-3">
                        <span class="text-stone-400 font-bold uppercase tracking-wider text-[9px] font-mono">Payment Status</span>
                        <span class="text-right font-mono">
                            @if($booking->payments->first() && $booking->payments->first()->status === 'paid')
                                <span class="px-2 py-0.5 text-[8px] uppercase tracking-wider font-bold rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Paid</span>
                            @else
                                <span class="px-2 py-0.5 text-[8px] uppercase tracking-wider font-bold rounded bg-[#faede7] text-[#c9512d] border border-[#c9512d]/30">Pay at Store</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-3 text-sm">
                        <span class="text-stone-900 font-bold uppercase tracking-wider text-[10px] font-mono">Total Amount</span>
                        <span class="text-[#c9512d] font-mono font-black text-base">Rp {{ number_format($booking->net_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-center space-x-4 font-mono">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-8 py-3.5 rounded-xl text-xs font-bold uppercase tracking-widest bg-[#c9512d] hover:bg-[#b74423] text-white transition shadow-sm">
                Back to Studio Home &rarr;
            </a>
        </div>
    </div>
@endsection
