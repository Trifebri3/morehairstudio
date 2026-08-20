<section class="bg-white py-12 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Rounded dark container -->
        <div class="bg-[#1c1917] rounded-[2.5rem] p-8 md:p-16 flex flex-col md:flex-row items-center justify-between gap-12 relative overflow-hidden shadow-2xl min-h-[500px]">
            <!-- Left Content Column -->
            <div class="md:w-3/5 space-y-6 relative z-10">
                <span class="text-[10px] uppercase font-extrabold tracking-widest text-[#dbeafe] bg-[#0A3D91] px-4 py-2 rounded-lg inline-block">
                    Premium Grooming &amp; Lifestyle
                </span>
                
                <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white uppercase leading-tight font-sans">
                    {{ \App\Domains\CMS\Services\CmsService::get('hero_tagline') }}
                </h1>
                
                <p class="text-stone-300 text-xs md:text-sm leading-relaxed max-w-lg font-light">
                    {{ \App\Domains\CMS\Services\CmsService::get('hero_description') }}
                </p>
                
                <div class="flex flex-wrap gap-4 pt-4">
                    <a href="{{ route('booking.index') }}" class="inline-flex items-center px-8 py-3.5 rounded-lg text-xs font-bold uppercase tracking-widest bg-[#0A3D91] hover:bg-[#062e70] text-white transition duration-300 shadow-md">
                        Book Your Experience
                    </a>
                    <a href="{{ route('services.index') }}" class="inline-flex items-center px-8 py-3.5 rounded-lg text-xs font-bold uppercase tracking-widest border border-stone-700 hover:bg-stone-850 text-stone-200 transition duration-300">
                        Explore More
                    </a>
                </div>
            </div>
            
            <!-- Right Image Column -->
            <div class="md:w-2/5 flex justify-center relative z-10">
                <div class="w-72 h-72 md:w-80 md:h-80 rounded-[2rem] overflow-hidden border-4 border-stone-800 shadow-2xl">
                    <img src="/images/hero_haircut.jpg" alt="MORE Experience" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</section>
