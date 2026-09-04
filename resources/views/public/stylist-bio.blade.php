@extends('layouts.public')

@section('title', $stylist->name . ' • Hair Artist Profile & Direct Booking | MORE Hair Studio')
@section('meta_description', 'Profil resmi ' . $stylist->name . ' di MORE Hair Studio Bandung. Spesialisasi ' . $stylist->specialization . '. Reservasi jadwal langsung tanpa antre.')

@section('content')
@php
    $outlet = $stylist->outlet;
    $outletName = $outlet?->name ?? 'MORE Hair Studio';
    $outletAddress = $outlet?->address ?? 'Jl. Mangga No. 37A, Cihapit, Bandung';
    $outletWa = $outlet?->whatsapp ?? '6282298347730';
    $instagramHandle = $stylist->instagram ? ltrim($stylist->instagram, '@') : null;
    $instagramUrl = $instagramHandle ? 'https://instagram.com/' . $instagramHandle : null;
    $bioUrl = url('/' . $stylist->slug);
@endphp

<div class="bg-[#fafaf9] min-h-screen py-10 sm:py-16 selection:bg-[#c9512d] selection:text-white font-sans"
     x-data="{ 
         copied: false,
         copyBioLink() {
             navigator.clipboard.writeText('{{ $bioUrl }}');
             this.copied = true;
             setTimeout(() => this.copied = false, 2500);
         }
     }">
    
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        
        <!-- Bio Card Container (Bento Luxury Style) -->
        <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-10 shadow-sm space-y-8 relative overflow-hidden">
            
            <!-- Decorative Subtle Accent Glow -->
            <div class="absolute -top-24 -right-24 w-60 h-60 bg-[#c9512d]/5 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Utility Bar: Share & Verified Badge -->
            <div class="flex items-center justify-between">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#f8eee8] border border-[#c9512d]/20 text-[#c9512d] text-xs font-mono font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-[#c9512d]"></span>
                    <span>Official MORE Hair Artist</span>
                </div>

                <!-- Tombol Salin Tautan Bio -->
                <button type="button" 
                        @click="copyBioLink()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-stone-200 hover:border-[#c9512d] bg-stone-50 hover:bg-white text-stone-600 hover:text-[#c9512d] text-xs font-mono font-bold transition shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span x-text="copied ? 'Tautan Disalin!' : 'Bagikan Link Bio'"></span>
                </button>
            </div>

            <!-- Profile Hero Section -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-6 pb-8 border-b border-stone-100">
                <!-- Avatar Stylist -->
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-stone-100 overflow-hidden border-2 border-[#c9512d]/30 shadow-md flex-shrink-0 relative flex items-center justify-center group">
                    @if($stylist->photo)
                        <img src="{{ Str::startsWith($stylist->photo, ['http', '/']) ? $stylist->photo : '/storage/' . $stylist->photo }}" 
                             alt="{{ $stylist->name }}" 
                             x-on:error="$el.style.display='none'; $el.nextElementSibling.classList.remove('hidden')"
                             class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                    @endif
                    <div class="{{ $stylist->photo ? 'hidden' : '' }} w-full h-full flex items-center justify-center font-bold text-stone-700 bg-stone-100 font-mono text-3xl uppercase">
                        {{ substr($stylist->name, 0, 1) }}
                    </div>
                </div>

                <!-- Info Stylist -->
                <div class="flex-grow space-y-3">
                    <div>
                        <h1 class="text-2xl sm:text-4xl font-black text-[#171615] tracking-tight uppercase font-headline">
                            {{ $stylist->name }}
                        </h1>
                        <span class="inline-block mt-1 text-xs font-mono uppercase tracking-widest text-[#c9512d] font-black">
                            {{ $stylist->specialization ?? 'Hair Specialist & Barber' }}
                        </span>
                    </div>

                    <!-- Rating & Reviews -->
                    <div class="flex items-center justify-center sm:justify-start gap-3 text-xs font-mono text-stone-500">
                        <span class="inline-flex items-center gap-1 font-bold text-amber-500">
                            ★ <span>{{ number_format($stylist->rating ?? 5.0, 1) }}</span>
                        </span>
                        <span>&bull;</span>
                        <span>MORE Certified Artist</span>
                        <span>&bull;</span>
                        <span>10:00 &ndash; 20:00 WIB</span>
                    </div>

                    <!-- Personal Bio Quote -->
                    <p class="text-xs sm:text-sm text-stone-600 italic font-serif leading-relaxed">
                        "{{ $stylist->bio ?? 'Setiap potongan rambut adalah kolaborasi personal untuk menemukan gaya yang paling mengekspresikan karakter diri Anda.' }}"
                    </p>

                    <!-- Social Media Links -->
                    <div class="flex items-center justify-center sm:justify-start gap-4 pt-1">
                        @if($instagramUrl)
                            <a href="{{ $instagramUrl }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-stone-600 hover:text-[#c9512d] transition">
                                <svg class="w-4 h-4 text-[#c9512d]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                <span>{{ '@' . $instagramHandle }}</span>
                            </a>
                        @endif

                        <a href="https://wa.me/{{ $outletWa }}?text=Halo%20MORE%20Hair%20Studio,%20saya%20mau%20konsultasi%20dengan%20{{ urlencode($stylist->name) }}." target="_blank" class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-stone-600 hover:text-emerald-600 transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.159.57 4.185 1.564 5.942l-1.564 5.702 5.841-1.533c1.704.928 3.652 1.458 5.723 1.458 6.627 0 12-5.373 12-12s-5.373-12-12-12z"/></svg>
                            <span>WhatsApp Studio</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Big Direct Booking CTA (Tidak Perlu Pilih Stylist Lagi) -->
            <div class="bg-gradient-to-br from-[#171615] to-[#262422] rounded-3xl p-6 sm:p-8 text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl relative overflow-hidden">
                <div class="space-y-1 text-center sm:text-left z-10">
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#c9512d] font-bold block">
                        Direct Booking Tanpa Antre
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black uppercase tracking-tight">
                        Booking Sesi dengan {{ $stylist->name }}
                    </h2>
                    <p class="text-xs text-stone-300 font-light">
                        Hair Artist otomatis terkunci &bull; Anda tidak perlu memilih stylist lagi.
                    </p>
                </div>

                <div class="z-10 flex-shrink-0 w-full sm:w-auto">
                    <a href="{{ route('booking.index', ['outlet_id' => $stylist->outlet_id, 'stylist_id' => $stylist->id]) }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-[#c9512d] hover:bg-[#b04322] text-white text-xs sm:text-sm font-black uppercase tracking-wider transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 text-center">
                        <span>Pesan Jadwal Sekarang</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Jam Booking Hari Ini (Live Jam Terisi) -->
            <div class="bg-stone-50 border border-stone-200 rounded-3xl p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-stone-200/80 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#c9512d] animate-pulse"></span>
                        <h3 class="text-xs font-mono uppercase tracking-widest text-[#171615] font-black">
                            Jadwal Booking Hari Ini ({{ \Carbon\Carbon::today()->translatedFormat('l, d M Y') }})
                        </h3>
                    </div>
                    <a href="{{ route('booking.check', ['tab' => 'slots', 'outlet_id' => $stylist->outlet_id]) }}" class="text-[11px] font-mono text-[#c9512d] hover:underline font-bold">
                        Cek Tanggal Lain &rarr;
                    </a>
                </div>

                @if($bookedToday->isNotEmpty())
                    <div class="space-y-3">
                        <span class="text-[10px] font-mono uppercase tracking-widest text-stone-400 font-bold block">
                            Jam Yang Sudah Terisi Pelanggan Lain:
                        </span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($bookedToday as $slot)
                                <div class="px-3 py-1.5 rounded-xl text-xs font-mono font-bold bg-[#f8eee8] text-[#c9512d] border border-[#c9512d]/30 flex items-center gap-1.5 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#c9512d]"></span>
                                    <span>Ada Booking: {{ $slot['start'] }} &ndash; {{ $slot['end'] }} WIB</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-stone-500 font-light flex items-center gap-1.5 pt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Jam di luar daftar di atas masih kosong dan dapat Anda pesan langsung.</span>
                        </p>
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-3 text-emerald-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <div>
                            <p class="text-xs font-bold">Belum Ada Booking Hari Ini</p>
                            <p class="text-[11px] text-emerald-700 font-light">Seluruh jam kerja (10:00 &ndash; 20:00 WIB) masih kosong dan siap untuk Anda pilih.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Signature Menu Layanan & Direct Booking per Treatment -->
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-[#171615]">
                            Pilihan Treatment &amp; Layanan
                        </h3>
                        <p class="text-xs text-stone-500 font-light mt-0.5">
                            Pilih treatment untuk langsung melompat ke pemilihan jam sesi dengan {{ $stylist->name }}.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    @foreach($services as $service)
                        <div class="border border-stone-200 rounded-2xl p-5 bg-white hover:border-[#c9512d] hover:shadow-sm transition duration-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-sm text-[#171615] uppercase tracking-tight group-hover:text-[#c9512d] transition">
                                        {{ $service->name }}
                                    </h4>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-stone-100 text-stone-600 font-semibold uppercase">
                                        {{ $service->category?->name ?? 'Grooming' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-xs font-mono text-stone-500">
                                    <span>Durasi: {{ $service->default_duration }} Menit</span>
                                    <span>&bull;</span>
                                    <span class="font-bold text-[#171615]">Rp {{ number_format($service->default_price, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Direct Link: Auto Preselect Stylist and Service, Skip to Step 3 (Pick Time) -->
                            <a href="{{ route('booking.index', ['outlet_id' => $stylist->outlet_id, 'stylist_id' => $stylist->id, 'service_id' => $service->id]) }}" 
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-stone-100 hover:bg-[#c9512d] text-stone-800 hover:text-white font-bold text-xs uppercase tracking-wider transition duration-200 flex-shrink-0 w-full sm:w-auto justify-center">
                                <span>Pilih Jam Sesi</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Studio Location Card -->
            <div class="pt-6 border-t border-stone-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs text-stone-500">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-stone-400 font-bold block">Lokasi Studio Resmi:</span>
                    <p class="font-bold text-[#171615] text-sm mt-0.5">{{ $outletName }}</p>
                    <p class="font-light text-stone-500">{{ $outletAddress }}</p>
                </div>
                <a href="{{ route('outlets.index') }}" class="text-xs font-mono font-bold text-[#c9512d] hover:underline flex-shrink-0">
                    Petunjuk Lokasi &rarr;
                </a>
            </div>

        </div>

    </div>
</div>
@endsection
