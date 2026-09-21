@props(['images'])

<section class="py-16 md:py-24 bg-stone-50 overflow-hidden relative border-t border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-5xl font-light text-stone-900 tracking-tight mb-4">Studio Highlights</h2>
            <p class="text-stone-500 font-light text-lg">Visual stories of our human-hair centered design philosophy.</p>
        </div>

        <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
            @foreach($images as $image)
                <div class="break-inside-avoid relative group rounded-2xl overflow-hidden cursor-pointer shadow-sm hover:shadow-lg transition-all duration-500">
                    <img src="{{ $image }}" alt="More Hair Studio Gallery" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-700 ease-in-out">
                    <div class="absolute inset-0 bg-stone-900/0 group-hover:bg-stone-900/20 transition-colors duration-500"></div>
                </div>
            @endforeach
        </div>
    </div>
</section>
