@props(['reviews'])

<section class="py-24 bg-white border-b border-stone-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-bold font-sans text-stone-900 mb-4 uppercase tracking-tight">Guest <span class="gold-gradient-text">Experiences</span></h2>
            <p class="text-xs text-stone-500 uppercase tracking-widest font-bold font-sans">Real Reviews from Verified Bookings</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($reviews as $review)
                <div class="border border-stone-200 bg-white p-6 rounded-2xl flex flex-col justify-between h-full hover:shadow transition animate-fade-in-up">
                    <div>
                        <!-- Rating Stars -->
                        <div class="flex space-x-1 mb-4 text-amber-500">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-lg">{{ $i <= $review->rating ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <p class="text-stone-500 text-xs italic leading-relaxed mb-6 font-light">
                            "{!! nl2br(e($review->review)) !!}"
                        </p>
                    </div>
                    <div class="border-t border-stone-100 pt-4 flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-bold text-stone-850 uppercase font-sans">{{ $review->customer->name }}</span>
                            <span class="block text-xxs text-stone-400 font-mono mt-0.5">{{ $review->outlet->name }}</span>
                        </div>
                        <span class="text-[10px] uppercase tracking-widest text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 font-extrabold font-mono">
                            Verified
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center text-stone-550 py-12">
                    Belum ada review yang disetujui.
                </div>
            @endforelse
        </div>
    </div>
</section>
