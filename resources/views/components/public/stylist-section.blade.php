@props(['stylists'])

<section class="py-24 bg-white border-t border-stone-150">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <h2 class="text-3xl md:text-5xl font-black text-stone-900 mb-4 uppercase tracking-tight">Meet your stylist</h2>
            <p class="text-xs text-stone-500 uppercase tracking-widest font-extrabold font-sans">Pilih pemotong andalan Anda di outlet terdekat</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($stylists as $stylist)
                <div class="border border-stone-200 bg-white rounded-2xl overflow-hidden flex flex-col hover:shadow transition">
                    <!-- Stylist Portrait placeholder -->
                    <div class="h-64 bg-stone-50 flex flex-col items-center justify-center relative overflow-hidden border-b border-stone-200">
                        <span class="text-stone-300 text-3xl font-extrabold select-none">
                            {{ collect(explode(' ', $stylist->name))->map(fn($n) => substr($n, 0, 1))->join('') }}
                        </span>
                        
                        <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur px-2.5 py-1 rounded-lg text-xxs text-stone-700 border border-stone-200 font-mono font-bold shadow-sm uppercase tracking-wider">
                            Rating: {{ number_format($stylist->rating, 1) }}
                        </div>
                    </div>
                    
                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <div>
                            <h4 class="text-base font-bold text-stone-900 font-sans mb-1 uppercase tracking-tight">{{ $stylist->name }}</h4>
                            <span class="text-[10px] text-[#0A3D91] uppercase font-extrabold tracking-wider block mb-3">
                                {{ $stylist->specialization }}
                            </span>
                            
                            <!-- Human-centric personality quote -->
                            <p class="text-stone-500 italic text-xs leading-relaxed mb-6 font-light">
                                @if($stylist->name === 'Raka')
                                    "I like cuts that look effortless but still feel intentional."
                                @else
                                    "Every service is a collaboration to find a style that expresses who you are."
                                @endif
                            </p>
                        </div>
                        
                        <div class="border-t border-stone-150 pt-4 flex items-center justify-between">
                            <span class="text-[9px] uppercase tracking-wider font-extrabold text-stone-400">{{ $stylist->outlet->name }}</span>
                            <a href="{{ route('booking.index') }}" class="text-xs font-bold text-[#0A3D91] hover:text-[#062e70] uppercase transition tracking-widest">
                                Book &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
