<section class="py-24 bg-white border-b border-stone-100 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <h2 class="text-3xl md:text-5xl font-black text-stone-900 mb-4 font-sans tracking-tight uppercase">
                {{ \App\Domains\CMS\Services\CmsService::get('why_title') }}
            </h2>
            <p class="text-[10px] text-stone-550 max-w-lg mx-auto leading-relaxed font-bold uppercase tracking-wider">
                {{ \App\Domains\CMS\Services\CmsService::get('why_subtitle') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="border border-stone-200 bg-stone-50/50 p-10 rounded-2xl transition hover:shadow-md">
                <span class="text-[10px] tracking-widest font-extrabold text-[#0A3D91] uppercase block mb-4">Quality 01</span>
                <h3 class="font-bold text-stone-900 text-base mb-2 font-sans uppercase">Precision Cuts</h3>
                <p class="text-stone-500 text-xs leading-relaxed font-light">
                    Setiap potongan dikerjakan dengan presisi tinggi menyesuaikan kontur wajah Anda untuk hasil yang paling maksimal.
                </p>
            </div>

            <div class="border border-stone-200 bg-stone-50/50 p-10 rounded-2xl transition hover:shadow-md">
                <span class="text-[10px] tracking-widest font-extrabold text-[#0A3D91] uppercase block mb-4">Comfort 02</span>
                <h3 class="font-bold text-stone-900 text-base mb-2 font-sans uppercase">Modern Lounge</h3>
                <p class="text-stone-500 text-xs leading-relaxed font-light">
                    Nikmati kopi premium gratis dan lounge nyaman saat Anda berkunjung ke studio kami.
                </p>
            </div>

            <div class="border border-stone-200 bg-stone-50/50 p-10 rounded-2xl transition hover:shadow-md">
                <span class="text-[10px] tracking-widest font-extrabold text-[#0A3D91] uppercase block mb-4">Digital 03</span>
                <h3 class="font-bold text-stone-900 text-base mb-2 font-sans uppercase">Seamless Booking</h3>
                <p class="text-stone-500 text-xs leading-relaxed font-light">
                    Sistem booking digital sat-set tanpa ribet, tanpa antre lama, dan tanpa perlu login.
                </p>
            </div>
        </div>
    </div>
</section>
