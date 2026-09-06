<section class="py-20 md:py-24 bg-white border-b border-stone-200 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left Column Content -->
            <div class="space-y-6">
                <span class="text-[10px] uppercase font-bold tracking-widest text-[#c9512d] bg-[#faede7] px-4 py-2 rounded-xl border border-[#c9512d]/25 inline-block font-mono">
                    (09) Tentang Kami &bull; Brand Story
                </span>
                
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight text-stone-900 leading-none uppercase font-primary">
                    {{ \App\Domains\CMS\Services\CmsService::get('about_tagline') }}
                </h2>
                
                <p class="text-stone-600 text-xs sm:text-sm leading-relaxed font-light font-secondary">
                    {{ \App\Domains\CMS\Services\CmsService::get('about_description_1') }}
                </p>
                
                <p class="text-stone-600 text-xs sm:text-sm leading-relaxed font-light font-secondary">
                    {{ \App\Domains\CMS\Services\CmsService::get('about_description_2') }}
                </p>

                <div class="pt-2">
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#c9512d] hover:text-[#b74423] font-mono transition">
                        <span>Baca Manifesto Selengkapnya di Brand Book</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
            
            <!-- Right Column Image -->
            <div class="flex justify-center">
                <div class="w-full h-80 md:h-96 rounded-3xl overflow-hidden shadow-xs border border-stone-200 bg-stone-50 group">
                    <img src="/images/about_tools.jpg" alt="Barber Tools" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
            </div>
        </div>
    </div>
</section>

