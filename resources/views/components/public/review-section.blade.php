@props(['reviews'])

<section class="py-20 md:py-24 bg-white border-b border-stone-200 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-mono uppercase tracking-widest text-[#c9512d] font-bold block mb-2">
                (11) Authentic Voice
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black font-primary text-stone-900 mb-3 uppercase tracking-tight">
                Guest <span class="text-[#c9512d]">Experiences</span>
            </h2>
            <p class="text-xs text-stone-500 uppercase tracking-widest font-mono">Real Reviews from Verified Studio Bookings</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($reviews as $review)
                <div class="border border-stone-200 bg-white p-7 rounded-3xl flex flex-col justify-between h-full hover:border-[#c9512d]/40 hover:shadow-xs transition duration-300 group">
                    <div>
                        <!-- Rating Stars -->
                        <div class="flex space-x-1 mb-4 text-[#c9512d]">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-base">{{ $i <= $review->rating ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <p class="text-stone-700 text-xs sm:text-sm italic font-serif leading-relaxed mb-6 font-light">
                            &ldquo;{!! nl2br(e($review->review)) !!}&rdquo;
                        </p>
                    </div>
                    <div class="border-t border-stone-100 pt-4 flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-bold text-stone-900 uppercase font-primary">{{ $review->customer->name }}</span>
                            <span class="block text-xxs text-stone-400 font-mono mt-0.5">{{ $review->outlet->name }}</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-widest text-[#c9512d] bg-[#faede7] px-2.5 py-1 rounded-md border border-[#c9512d]/25 font-bold font-mono">
                            Verified Guest
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center text-stone-500 py-12 font-mono text-xs">
                    Belum ada review yang disetujui.
                </div>
            @endforelse
        </div>
    </div>
</section>

