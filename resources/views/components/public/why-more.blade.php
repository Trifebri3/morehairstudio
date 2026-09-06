<section class="py-20 md:py-24 bg-white border-b border-stone-200 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-mono uppercase tracking-widest text-[#c9512d] font-bold block mb-2">
                (03) Quality &amp; Innovation
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-stone-900 mb-4 font-primary tracking-tight uppercase">
                {{ \App\Domains\CMS\Services\CmsService::get('why_title') }}
            </h2>
            <p class="text-xs text-stone-500 max-w-lg mx-auto leading-relaxed font-mono uppercase tracking-wider">
                {{ \App\Domains\CMS\Services\CmsService::get('why_subtitle') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="border border-stone-200 bg-white p-8 rounded-3xl transition hover:border-[#c9512d]/40 hover:shadow-xs group">
                <span class="text-xs tracking-widest font-bold text-[#c9512d] font-mono uppercase block mb-4">Quality 01</span>
                <h3 class="font-bold text-stone-900 text-lg mb-2 font-primary uppercase tracking-tight">Precision Cuts</h3>
                <p class="text-stone-600 text-xs sm:text-sm leading-relaxed font-light font-secondary">
                    Setiap potongan dikerjakan dengan presisi tinggi menyesuaikan kontur wajah, tekstur helai rambut, dan bentuk tengkorak Anda untuk siluet paling optimal.
                </p>
            </div>

            <div class="border border-stone-200 bg-white p-8 rounded-3xl transition hover:border-[#c9512d]/40 hover:shadow-xs group">
                <span class="text-xs tracking-widest font-bold text-[#c9512d] font-mono uppercase block mb-4">Comfort 02</span>
                <h3 class="font-bold text-stone-900 text-lg mb-2 font-primary uppercase tracking-tight">Modern Lounge</h3>
                <p class="text-stone-600 text-xs sm:text-sm leading-relaxed font-light font-secondary">
                    Nikmati sajian kopi premium terkurasi dan lounge atmosferik yang nyaman saat Anda rehat di sanctuary kami di Kota Bandung.
                </p>
            </div>

            <div class="border border-stone-200 bg-white p-8 rounded-3xl transition hover:border-[#c9512d]/40 hover:shadow-xs group">
                <span class="text-xs tracking-widest font-bold text-[#c9512d] font-mono uppercase block mb-4">Digital 03</span>
                <h3 class="font-bold text-stone-900 text-lg mb-2 font-primary uppercase tracking-tight">Seamless Booking</h3>
                <p class="text-stone-600 text-xs sm:text-sm leading-relaxed font-light font-secondary">
                    Sistem pemesanan digital instan tanpa ribet unduh aplikasi, tanpa antre lama, dan tiket otomatis tersimpan di WhatsApp Anda.
                </p>
            </div>
        </div>
    </div>
</section>

