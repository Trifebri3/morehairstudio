<section class="py-20 bg-white border-b border-stone-100 relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
        <h2 class="text-3xl md:text-4xl font-bold font-sans text-stone-900 mb-6 uppercase">Loved Your <span class="gold-gradient-text">Experience?</span></h2>
        <p class="text-stone-500 text-sm max-w-lg mx-auto mb-8 font-light">
            Share your feedback on our stylists and treatments! Verified customers receive booking rewards directly in their emails.
        </p>
        <x-ui.button variant="outline" onclick="window.location.href='{{ route('booking.index') }}'">
            Share Feedback
        </x-ui.button>
    </div>
</section>
