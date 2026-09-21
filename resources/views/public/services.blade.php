@extends('layouts.public')

@section('title', 'Layanan & Tarif | MORE Hair Studio Bandung')
@section('meta_description', 'Daftar lengkap layanan presisi, chemical treatment, keratin, perm, dan pewarnaan MORE Hair Studio Bandung.')

@section('content')
<style>
/* ── SERVICES PAGE FONTS ── */
.sv-badge   { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 400; }
.sv-h1      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.05; letter-spacing: -0.03em; }
.sv-sub     { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; line-height: 1.6; }
.sv-cat     { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 400; }
.sv-price   { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; }
.sv-h3      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; letter-spacing: -0.01em; }
.sv-desc    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; line-height: 1.6; }
.sv-btn     { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; letter-spacing: 0.05em; }
</style>

<div class="bg-white min-h-screen py-16 md:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <!-- Header -->
        <div class="border-b border-stone-200 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-2">
                <span class="sv-badge text-[#c9512d] block">
                    Layanan &amp; Perawatan
                </span>
                <h1 class="sv-h1 text-3xl sm:text-4xl md:text-5xl text-stone-900">
                    Menu &amp; Tarif Layanan
                </h1>
                <p class="sv-sub text-stone-600 text-xs sm:text-sm max-w-xl leading-relaxed">
                    Setiap ritual dirancang dengan ketelitian tinggi—menyelaraskan karakter helai rambut dengan proporsi visual wajah Anda.
                </p>
            </div>
            <div>
                <a href="{{ route('booking.index') }}" class="sv-btn inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs uppercase bg-[#c9512d] hover:bg-[#b74423] text-white transition duration-200 shadow-sm">
                    <span>Mulai Reservasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            @foreach($services as $service)
                <div class="bg-white border border-stone-200 p-6 sm:p-7 rounded-2xl flex flex-col justify-between hover:border-[#c9512d]/40 transition duration-200 shadow-sm group">
                    <div class="space-y-3.5">
                        <!-- Top Row: Category Badge & Price -->
                        <div class="flex justify-between items-start gap-4">
                            <span class="sv-cat bg-[#faede7] text-[#c9512d] px-2.5 py-1 rounded-md inline-block">
                                {{ $service->category->name ?? 'Layanan' }}
                            </span>
                            <div class="text-right">
                                <span class="sv-price text-base sm:text-lg text-[#c9512d] block">
                                    Rp {{ number_format($service->default_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Service Title -->
                        <h3 class="sv-h3 text-xl text-stone-900">{{ $service->name }}</h3>

                        <!-- Rich Formatted Description -->
                        <div class="sv-desc text-xs text-stone-600">
                            {!! $service->formatted_description_html !!}
                        </div>
                    </div>

                    <!-- Bottom Bar: Duration & Booking Action -->
                    <div class="pt-5 mt-6 border-t border-stone-100 flex justify-between items-center">
                        <div class="text-stone-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="sv-desc text-xs">Estimasi:</span>
                            <span class="sv-desc text-xs text-stone-700 font-semibold">{{ $service->default_duration }} Menit</span>
                        </div>
                        <a href="{{ route('booking.index', ['service_id' => $service->id]) }}" class="sv-btn inline-flex items-center px-5 py-2.5 rounded-xl text-xs bg-stone-900 hover:bg-[#c9512d] text-white transition duration-200">
                            Pesan
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Bottom Note -->
        <div class="pt-8 border-t border-stone-100 flex flex-col sm:flex-row justify-between items-center text-xs text-stone-500 gap-2">
            <span class="sv-desc">Setiap haircut sudah termasuk 10-menit Define Session, cuci rambut relaksasi, dan styling.</span>
            <a href="{{ route('outlets.index') }}" class="sv-btn text-[#c9512d] hover:underline">Lihat Jadwal Per Cabang &rarr;</a>
        </div>

    </div>
</div>
@endsection
