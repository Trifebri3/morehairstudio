@extends('layouts.public')

@section('title', $stylist->name . ' - ' . ($stylist->specialization ?? 'Hair Artist') . ' | MORE Hair Studio')
@section('meta_description', 'Profil resmi Hair Artist ' . $stylist->name . ' di MORE Hair Studio Bandung. Lihat portofolio, jadwal kerja, daftar tarif, dan pesan jadwal langsung tanpa antre.')

@section('content')
<div class="bg-[#fafaf9] min-h-screen py-10 md:py-16 font-sans text-stone-900" x-data="stylistProfilePage()">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- Breadcrumb & Back Link -->
        <div class="flex items-center justify-between text-xs text-stone-500">
            <a href="{{ route('stylists.index') }}" class="inline-flex items-center gap-2 hover:text-[#c9512d] transition font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Semua Hair Artists</span>
            </a>
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="copyProfileUrl()" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-stone-200 bg-white hover:border-[#c9512d] hover:text-[#c9512d] text-stone-600 transition shadow-2xs text-[11px] font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span x-text="copied ? 'Link Tersalin!' : 'Salin Link Profil'"></span>
                </button>
            </div>
        </div>

        <!-- Hero Profile Card -->
        <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-10 shadow-sm relative overflow-hidden">
            <!-- Subtle background accent -->
            <div class="absolute top-0 right-0 w-80 h-80 bg-gradient-to-bl from-[#faede7]/60 via-stone-50/40 to-transparent rounded-full blur-3xl -z-10 pointer-events-none"></div>

            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                <!-- Artist Portrait -->
                <div class="relative flex-shrink-0">
                    <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl overflow-hidden border-2 border-stone-200 shadow-md bg-stone-100 flex items-center justify-center relative">
                        <img src="{{ $stylist->display_photo }}" 
                             alt="{{ $stylist->name }}" 
                             class="w-full h-full object-cover">
                    </div>
                    <span class="absolute bottom-2 right-2 px-2.5 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full border-2 border-white shadow-sm flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                        Aktif
                    </span>
                </div>

                <!-- Artist Main Details -->
                <div class="flex-grow text-center md:text-left space-y-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#c9512d] bg-[#faede7] px-3 py-1 rounded-full">
                                {{ $stylist->specialization ?: 'Hair Artist & Stylist' }}
                            </span>
                            <span class="text-[11px] font-semibold text-stone-500 bg-stone-100 px-3 py-1 rounded-full flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Verified Artist
                            </span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-stone-900 tracking-tight pt-2">
                            {{ $stylist->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-stone-500 font-normal">
                            Penata Rambut Spesialis MORE Hair Studio
                        </p>
                    </div>

                    <!-- Outlet & Rating Badge -->
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 pt-1">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-bold">
                            <span class="text-amber-500 text-sm">★</span>
                            <span>{{ number_format($stylist->rating ?: 5.0, 1) }}</span>
                            <span class="text-amber-700/60 font-normal text-[11px]">• Rating Sempurna</span>
                        </div>

                        @if($outlet)
                            <a href="{{ route('outlets.show', $outlet->slug) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-stone-200/70 border border-stone-200 text-stone-700 text-xs font-semibold transition">
                                <svg class="w-3.5 h-3.5 text-[#c9512d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $outlet->name }}</span>
                            </a>
                        @endif

                        @if($completedCount > 0)
                            <div class="text-xs text-stone-500 font-medium">
                                <strong class="text-stone-900 font-bold">{{ $completedCount }}+</strong> Sesi Potong Selesai
                            </div>
                        @endif
                    </div>

                    <!-- Social Channels -->
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 pt-2">
                        @if($stylist->instagram)
                            <a href="https://instagram.com/{{ ltrim($stylist->instagram, '@') }}" 
                               target="_blank" 
                               rel="noopener" 
                               class="inline-flex items-center gap-1 text-xs font-medium text-stone-600 hover:text-[#c9512d] transition">
                                <span class="font-bold text-stone-400">IG:</span> {{ $stylist->instagram }}
                            </a>
                        @endif
                        @if($stylist->tiktok)
                            <span class="text-stone-300 hidden sm:inline">•</span>
                            <a href="https://tiktok.com/@{{ ltrim($stylist->tiktok, '@') }}" 
                               target="_blank" 
                               rel="noopener" 
                               class="inline-flex items-center gap-1 text-xs font-medium text-stone-600 hover:text-[#c9512d] transition">
                                <span class="font-bold text-stone-400">TikTok:</span> {{ $stylist->tiktok }}
                            </a>
                        @endif
                        @if($stylist->phone)
                            <span class="text-stone-300 hidden sm:inline">•</span>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $stylist->phone) }}?text=Halo%20{{ urlencode($stylist->name) }},%20saya%20tertarik%20konsultasi%20gaya%20rambut%20di%20MORE%20Hair%20Studio" 
                               target="_blank" 
                               rel="noopener" 
                               class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 hover:text-emerald-800 transition">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Tanya via WhatsApp
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Instant Booking Hero CTA Button -->
                <div class="w-full md:w-auto flex flex-col sm:flex-row md:flex-col gap-3 flex-shrink-0">
                    <a href="{{ route('booking.index', ['stylist_id' => $stylist->id]) }}" 
                       class="w-full px-7 py-4 rounded-2xl bg-[#c9512d] hover:bg-[#b74423] text-white text-center font-bold text-xs uppercase tracking-wider transition duration-200 shadow-md flex items-center justify-center gap-2 group">
                        <span>Pesan Jadwal Sekarang</span>
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <p class="text-[11px] text-stone-500 text-center font-normal">
                        Langsung terhubung tanpa perlu pilih artist lagi
                    </p>
                </div>
            </div>
        </div>

        <!-- 2 Column Grid: Bio & Working Schedule -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Left: Bio & Style Manifesto (2 Cols) -->
            <div class="md:col-span-2 space-y-8">
                <!-- Bio Card -->
                <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#c9512d] block">
                        Tentang &amp; Filosofi Artist
                    </span>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight">
                        Dedikasi Presisi &amp; Karakter Rambut Personal
                    </h2>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed font-normal">
                        {{ $stylist->bio ?? 'Mendedikasikan keahlian presisi, pemahaman anatomi kepala dan arah pertumbuhan rambut alami untuk menciptakan siluet potongan yang tahan lama, rapi, dan mudah ditata sendiri di rumah.' }}
                    </p>

                    <!-- Core Strengths Tags -->
                    <div class="pt-4 border-t border-stone-100">
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-3">
                            Spesialisasi &amp; Teknik Utama
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200 text-stone-700 text-xs font-medium">Precision Scissor Cut</span>
                            <span class="px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200 text-stone-700 text-xs font-medium">Skin Fade &amp; Taper Fade</span>
                            <span class="px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200 text-stone-700 text-xs font-medium">Textured Mullet &amp; Crop</span>
                            <span class="px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200 text-stone-700 text-xs font-medium">Design Perm &amp; Down Perm</span>
                            <span class="px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200 text-stone-700 text-xs font-medium">Hair Architecture Consultation</span>
                        </div>
                    </div>
                </div>

                <!-- Direct Services Menu Section -->
                <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 border-b border-stone-100 pb-4">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#c9512d] block">
                                Pilihan Layanan
                            </span>
                            <h3 class="text-xl font-extrabold text-stone-900 tracking-tight">
                                Pesan Langsung Bersama {{ $stylist->name }}
                            </h3>
                            <p class="text-xs text-stone-500 mt-1">
                                Klik layanan di bawah untuk langsung menuju tahap penentuan waktu dengan artist ini.
                            </p>
                        </div>
                    </div>

                    <!-- Services Listing by Category -->
                    <div class="space-y-6">
                        @forelse($servicesByCategory as $catName => $items)
                            <div class="space-y-3">
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-stone-400 pb-1 border-b border-stone-150">
                                    {{ $catName }}
                                </h4>
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($items as $svc)
                                        <div class="p-4 rounded-2xl border border-stone-200 hover:border-[#c9512d] bg-white hover:bg-[#fafaf9] transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                                            <div class="space-y-1">
                                                <h5 class="font-bold text-stone-900 text-xs uppercase tracking-tight group-hover:text-[#c9512d] transition-colors">
                                                    {{ $svc['name'] }}
                                                </h5>
                                                <div class="flex items-center gap-2 text-[11px] text-stone-500">
                                                    <span>Durasi: <strong class="text-stone-700 font-semibold">{{ $svc['duration'] }} Menit</strong></span>
                                                    <span>•</span>
                                                    <span class="font-bold text-[#c9512d]">Rp {{ number_format($svc['price'], 0, ',', '.') }}</span>
                                                </div>
                                                @if(!empty($svc['description']))
                                                    <p class="text-[11px] text-stone-500 font-normal line-clamp-1 mt-0.5">
                                                        {{ $svc['description'] }}
                                                    </p>
                                                @endif
                                            </div>

                                            <a href="{{ route('booking.index', ['stylist_id' => $stylist->id, 'service_id' => $svc['id']]) }}" 
                                               class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-stone-900 hover:bg-[#c9512d] text-white text-xs font-semibold uppercase tracking-wider transition duration-150 text-center flex-shrink-0 flex items-center justify-center gap-1.5">
                                                <span>Pilih Layanan</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-stone-500">Belum ada daftar layanan khusus terlampir.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Schedule & Studio Info (1 Col) -->
            <div class="space-y-8">
                
                <!-- Working Schedule Card -->
                <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm space-y-4">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#c9512d] block">
                        Jadwal &amp; Jam Bertugas
                    </span>
                    <h3 class="text-base font-extrabold text-stone-900 tracking-tight">
                        Waktu Praktik Studio
                    </h3>

                    <div class="space-y-2.5 pt-2">
                        @php
                            $schedules = $stylist->schedules->keyBy('day_of_week');
                        @endphp

                        @for($d = 0; $d <= 6; $d++)
                            @php
                                $sch = $schedules->get($d);
                                $isWork = $sch && $sch->is_working;
                                $dayName = $daysMap[$d] ?? 'Hari';
                            @endphp
                            <div class="flex items-center justify-between text-xs py-1.5 border-b border-stone-100 last:border-none">
                                <span class="font-medium {{ $isWork ? 'text-stone-800' : 'text-stone-400' }}">
                                    {{ $dayName }}
                                </span>
                                @if($isWork)
                                    <span class="font-mono text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                        {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} WIB
                                    </span>
                                @else
                                    <span class="font-mono text-[11px] text-stone-400 bg-stone-50 px-2 py-0.5 rounded-md">
                                        Libur
                                    </span>
                                @endif
                            </div>
                        @endfor
                    </div>

                    <div class="pt-3 border-t border-stone-100 text-[11px] text-stone-500 leading-relaxed font-normal">
                        * Slot jam dapat dipilih secara real-time saat melakukan booking jadwal.
                    </div>
                </div>

                <!-- Studio Outlet Card -->
                @if($outlet)
                    <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm space-y-4">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#c9512d] block">
                            Lokasi Studio Bertugas
                        </span>
                        <div>
                            <h4 class="text-base font-extrabold text-stone-900">{{ $outlet->name }}</h4>
                            <p class="text-xs text-stone-500 mt-1 leading-relaxed">{{ $outlet->address }}</p>
                        </div>

                        <div class="pt-2">
                            <a href="{{ route('outlets.show', $outlet->slug) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#c9512d] hover:text-[#b74423]">
                                <span>Lihat Fasilitas &amp; Maps Studio</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Share Link Box -->
                <div class="bg-gradient-to-br from-stone-900 to-stone-800 text-white rounded-3xl p-6 shadow-sm space-y-3">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-[#c9512d] block">
                        Bagikan Profil Ini
                    </span>
                    <h4 class="text-sm font-bold text-white">
                        Link Booking Pribadi {{ $stylist->name }}
                    </h4>
                    <p class="text-xs text-stone-300 font-light leading-relaxed">
                        Salin link ini untuk disimpan atau dibagikan ke teman dan media sosial:
                    </p>
                    <div class="p-2.5 rounded-xl bg-white/10 border border-white/15 text-[11px] font-mono break-all text-stone-200 select-all">
                        {{ url('/' . ($stylist->slug ?: \Illuminate\Support\Str::slug($stylist->name))) }}
                    </div>
                    <button type="button" 
                            @click="copyProfileUrl()" 
                            class="w-full py-2.5 rounded-xl bg-[#c9512d] hover:bg-[#b74423] text-white text-xs font-bold uppercase tracking-wider transition">
                        <span x-text="copied ? '✓ Berhasil Tersalin' : 'Salin URL Profil'"></span>
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- Mobile Floating Booking Sticky Bar -->
    <div class="md:hidden fixed bottom-16 left-0 right-0 p-3 bg-white/95 backdrop-blur-md border-t border-stone-200 z-40 shadow-lg flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 overflow-hidden">
            <img src="{{ $stylist->display_photo }}" alt="{{ $stylist->name }}" class="w-10 h-10 rounded-xl object-cover border border-stone-200 flex-shrink-0">
            <div class="truncate">
                <h5 class="text-xs font-extrabold text-stone-900 truncate">{{ $stylist->name }}</h5>
                <span class="text-[10px] text-[#c9512d] font-bold block truncate">{{ $stylist->specialization ?: 'Hair Artist' }}</span>
            </div>
        </div>
        <a href="{{ route('booking.index', ['stylist_id' => $stylist->id]) }}" 
           class="px-5 py-2.5 rounded-xl bg-[#c9512d] text-white text-xs font-bold uppercase tracking-wider shadow-sm flex-shrink-0">
            Pesan Jadwal
        </a>
    </div>

    <!-- Toast Notification Component -->
    <div x-show="showToast" 
         x-transition 
         class="fixed bottom-20 right-6 bg-stone-900 text-white px-4 py-2.5 rounded-xl text-xs font-semibold shadow-xl z-50 flex items-center gap-2 border border-stone-700"
         style="display: none;">
        <span>✓ Link profil berhasil disalin ke clipboard!</span>
    </div>
</div>

<script>
function stylistProfilePage() {
    return {
        copied: false,
        showToast: false,
        copyProfileUrl() {
            const url = "{{ url('/' . ($stylist->slug ?: \Illuminate\Support\Str::slug($stylist->name))) }}";
            navigator.clipboard.writeText(url).then(() => {
                this.copied = true;
                this.showToast = true;
                setTimeout(() => {
                    this.copied = false;
                    this.showToast = false;
                }, 3000);
            }).catch(() => {
                // Fallback prompt
                prompt("Salin link profil ini:", url);
            });
        }
    }
}
</script>
@endsection
