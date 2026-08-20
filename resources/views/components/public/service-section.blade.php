@props(['services'])

<section id="services" class="py-24 bg-white border-b border-[#e7e5e4]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16">
            <div>
                <h2 class="text-3xl md:text-5xl font-black text-stone-900 mb-4 uppercase tracking-tight">How would you like to experience More?</h2>
                <p class="text-xs text-stone-500 uppercase tracking-widest font-extrabold font-sans">Designed for Ultimate Style and Health</p>
            </div>
            <a href="{{ route('services.index') }}" class="text-xs uppercase tracking-widest text-[#0A3D91] hover:text-[#062e70] font-extrabold mt-4 md:mt-0 transition duration-300">
                View All Services &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($services as $service)
                <div class="border border-stone-200 bg-white p-6 rounded-2xl flex justify-between items-start space-x-4 hover:shadow transition">
                    <div class="flex-grow">
                        <span class="text-[10px] font-extrabold text-[#0A3D91] uppercase tracking-wider block mb-1">
                            {{ $service->category->name }}
                        </span>
                        <h4 class="text-base font-bold text-stone-900 font-sans mb-2 uppercase">{{ $service->name }}</h4>
                        <p class="text-stone-500 text-xs leading-relaxed max-w-lg mb-4 font-light">{{ $service->description }}</p>
                        <span class="text-[10px] text-stone-400 font-mono uppercase font-bold">Duration: {{ $service->default_duration }} Min</span>
                    </div>
                    <div class="text-right flex flex-col justify-between h-full min-h-[100px] items-end">
                        <span class="text-sm font-bold font-mono text-[#0A3D91] block">
                            Rp {{ number_format($service->default_price, 0, ',', '.') }}
                        </span>
                        <x-ui.button variant="outline" size="sm" onclick="window.location.href='{{ route('booking.index') }}'" class="mt-4">
                            Book Experience
                        </x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
