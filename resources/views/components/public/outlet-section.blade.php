@props(['outlets'])

<section class="py-24 bg-white border-b border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16">
            <div>
                <h2 class="text-3xl md:text-5xl font-black text-stone-900 mb-4 uppercase tracking-tight">Our Studios</h2>
                <p class="text-xs text-stone-500 uppercase tracking-widest font-extrabold font-sans">Select Locations Near You</p>
            </div>
            <a href="{{ route('outlets.index') }}" class="text-xs uppercase tracking-widest text-[#0A3D91] hover:text-[#062e70] font-extrabold mt-4 md:mt-0 transition duration-300">
                All Locations &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($outlets as $outlet)
                <div class="border border-stone-200 bg-white p-8 rounded-2xl flex flex-col justify-between hover:border-[#0A3D91]/30 hover:shadow transition duration-300">
                    <div>
                        <span class="text-[10px] uppercase tracking-widest text-[#0A3D91] font-extrabold bg-blue-50 px-3 py-1 rounded border border-blue-100 inline-block mb-4">
                            Studio
                        </span>
                        <h4 class="text-xl font-bold text-stone-900 font-sans mb-2 uppercase">{{ $outlet->name }}</h4>
                        <p class="text-stone-500 text-xs leading-relaxed mb-6 font-light">{{ $outlet->description }}</p>
                        
                        <div class="space-y-3 border-t border-stone-100 pt-4 text-xs text-stone-600">
                            <p class="flex items-start space-x-2 leading-relaxed">
                                <span class="font-extrabold uppercase text-[9px] tracking-wider text-stone-400 block min-w-[60px] mt-0.5">Address:</span>
                                <span>{{ $outlet->address }}</span>
                            </p>
                            <p class="flex items-center space-x-2">
                                <span class="font-extrabold uppercase text-[9px] tracking-wider text-stone-400 block min-w-[60px]">Phone:</span>
                                <span>{{ $outlet->phone }}</span>
                            </p>
                            <p class="flex items-center space-x-2">
                                <span class="font-extrabold uppercase text-[9px] tracking-wider text-stone-400 block min-w-[60px]">WhatsApp:</span>
                                <span>+{{ $outlet->whatsapp }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-8 pt-4 border-t border-stone-100 flex items-center justify-between">
                        <span class="text-[9px] uppercase text-stone-400 font-mono">Location coordinates: {{ $outlet->latitude }}, {{ $outlet->longitude }}</span>
                        <x-ui.button variant="primary" size="sm" onclick="window.location.href='{{ route('booking.index') }}'">
                            Select Studio
                        </x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
