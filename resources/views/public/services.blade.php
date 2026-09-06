@extends('layouts.public')

@section('title', 'Layanan & Tarif | MORE Hair Studio Bandung')
@section('meta_description', 'Daftar lengkap layanan presisi, chemical treatment, keratin, perm, dan pewarnaan MORE Hair Studio Bandung.')

@section('content')
<div class="bg-white min-h-screen py-16 md:py-24 font-sans">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <!-- Header -->
        <div class="border-b border-stone-200 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-2">
                <span class="text-xs uppercase tracking-wider text-[#c9512d] font-semibold block">
                    Layanan &amp; Perawatan
                </span>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-stone-900 tracking-tight">
                    Menu &amp; Tarif Layanan
                </h1>
                <p class="text-stone-600 text-xs sm:text-sm max-w-xl font-normal leading-relaxed">
                    Setiap ritual dirancang dengan ketelitian tinggi—menyelaraskan karakter helai rambut dengan proporsi visual wajah Anda.
                </p>
            </div>
            <div>
                <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-semibold uppercase tracking-wider bg-[#c9512d] hover:bg-[#b74423] text-white transition duration-200 shadow-2xs">
                    <span>Mulai Reservasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            @foreach($services as $service)
                <div class="bg-white border border-stone-200 p-6 sm:p-7 rounded-2xl flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200 shadow-2xs hover:shadow-xs group">
                    <div class="space-y-3.5">
                        <!-- Top Row: Category Badge & Price -->
                        <div class="flex justify-between items-start gap-4">
                            <span class="text-[10px] uppercase tracking-wider text-[#c9512d] font-bold bg-[#faede7] px-2.5 py-1 rounded-md inline-block font-mono">
                                {{ $service->category->name ?? 'Layanan' }}
                            </span>
                            <div class="text-right">
                                <span class="text-base sm:text-lg font-bold text-[#c9512d] tracking-tight block">
                                    Rp {{ number_format($service->default_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Service Title -->
                        <h3 class="text-lg font-bold text-stone-900 tracking-tight">{{ $service->name }}</h3>

                        <!-- Rich Formatted Description -->
                        <div class="service-description">
                            {!! $service->formatted_description_html !!}
                        </div>
                    </div>

                    <!-- Bottom Bar: Duration & Booking Action -->
                    <div class="pt-5 mt-6 border-t border-stone-100 flex justify-between items-center text-xs">
                        <div class="text-stone-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Estimasi:</span>
                            <span class="font-semibold text-stone-700">{{ $service->default_duration }} Menit</span>
                        </div>
                        <a href="{{ route('booking.index', ['service_id' => $service->id]) }}" class="inline-flex items-center px-5 py-2.5 rounded-xl text-xs font-semibold bg-stone-900 hover:bg-[#c9512d] text-white transition duration-200 shadow-2xs">
                            Pesan
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Bottom Note -->
        <div class="pt-8 border-t border-stone-100 flex flex-col sm:flex-row justify-between items-center text-xs text-stone-500 gap-2">
            <span>Setiap haircut sudah termasuk 10-menit Define Session, cuci rambut relaksasi, dan styling.</span>
            <a href="{{ route('outlets.index') }}" class="text-[#c9512d] font-semibold hover:underline">Lihat Jadwal Per Cabang &rarr;</a>
        </div>

    </div>
</div>
@endsection
