<section class="py-20 bg-white font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left Column Content -->
            <div class="space-y-6">
                <span class="text-[10px] uppercase font-extrabold tracking-widest text-[#0A3D91] bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 inline-block">
                    Tentang Kami
                </span>
                
                <h2 class="text-3xl md:text-5xl font-black tracking-tight text-stone-900 leading-none uppercase font-sans">
                    {{ \App\Domains\CMS\Services\CmsService::get('about_tagline') }}
                </h2>
                
                <p class="text-stone-500 text-sm leading-relaxed font-light">
                    {{ \App\Domains\CMS\Services\CmsService::get('about_description_1') }}
                </p>
                
                <p class="text-stone-500 text-sm leading-relaxed font-light">
                    {{ \App\Domains\CMS\Services\CmsService::get('about_description_2') }}
                </p>
            </div>
            
            <!-- Right Column Image -->
            <div class="flex justify-center">
                <div class="w-full h-80 rounded-[2rem] overflow-hidden shadow-xl border border-stone-100">
                    <img src="/images/about_tools.jpg" alt="Barber Tools" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</section>
