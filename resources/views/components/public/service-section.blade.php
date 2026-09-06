@props(['services'])

<section id="services" class="py-20 md:py-24 bg-white border-b border-stone-200 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-4">
            <div>
                <span class="text-xs font-mono uppercase tracking-widest text-[#c9512d] font-bold block mb-2">
                    (06) Spektrum Treatment
                </span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-stone-900 mb-3 uppercase tracking-tight font-primary">
                    How would you like to experience More?
                </h2>
                <p class="text-xs text-stone-500 uppercase tracking-widest font-mono">
                    Designed for Ultimate Style and Hair Integrity
                </p>
            </div>
            <a href="{{ route('services.index') }}" class="text-xs uppercase tracking-widest text-[#c9512d] hover:text-[#b74423] font-bold font-mono transition duration-200 inline-flex items-center gap-1">
                <span>View Full Menu</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($services as $service)
                <div class="border border-stone-200 bg-white p-7 rounded-3xl flex justify-between items-start space-x-6 hover:border-[#c9512d]/40 hover:shadow-xs transition duration-300 group">
                    <div class="flex-grow space-y-2">
                        <span class="text-[10px] font-bold text-[#c9512d] uppercase tracking-wider block font-mono">
                            {{ $service->category->name }}
                        </span>
                        <h4 class="text-lg font-bold text-stone-900 font-primary uppercase tracking-tight">{{ $service->name }}</h4>
                        <p class="text-stone-600 text-xs sm:text-sm leading-relaxed max-w-lg font-light font-secondary">{{ $service->description }}</p>
                        <div class="pt-2">
                            <span class="text-[10px] text-stone-400 font-mono uppercase font-bold">Duration: {{ $service->default_duration }} Min</span>
                        </div>
                    </div>
                    <div class="text-right flex flex-col justify-between h-full min-h-[110px] items-end shrink-0">
                        <span class="text-base font-bold font-mono text-[#c9512d] block">
                            Rp {{ number_format($service->default_price, 0, ',', '.') }}
                        </span>
                        <a href="{{ route('booking.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-stone-50 hover:bg-[#c9512d] text-stone-800 hover:text-white border border-stone-200 hover:border-[#c9512d] transition duration-200 shadow-2xs">
                            Book
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

