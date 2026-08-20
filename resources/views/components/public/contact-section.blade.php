<section class="py-24 bg-white border-b border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Details -->
            <div class="space-y-6">
                <span class="text-[10px] uppercase tracking-widest text-[#0A3D91] bg-blue-50 px-4 py-2 rounded-full border border-blue-100 inline-block font-extrabold font-sans">
                    Get In Touch
                </span>
                <h2 class="text-3xl md:text-5xl font-black text-stone-900 leading-tight uppercase font-sans tracking-tight">
                    Visit Our <br><span class="gold-gradient-text">Studio Lounge</span>
                </h2>
                <p class="text-stone-500 text-sm leading-relaxed font-light">
                    Have questions about our signature coloring treatments or private room bookings? Contact us or drop by our studios.
                </p>
                <div class="space-y-4 pt-4">
                    <div class="flex items-center space-x-3 text-xs text-stone-600 font-medium">
                        <span class="font-bold uppercase text-[9px] tracking-wider text-stone-400 block min-w-[50px]">Email:</span>
                        <span>contact@morehairstudio.com</span>
                    </div>
                    <div class="flex items-center space-x-3 text-xs text-stone-600 font-medium">
                        <span class="font-bold uppercase text-[9px] tracking-wider text-stone-400 block min-w-[50px]">Hours:</span>
                        <span>Monday - Sunday: 10:00 AM - 08:00 PM</span>
                    </div>
                </div>
            </div>

            <!-- Simple Contact Mockup Form -->
            <div class="border border-stone-200 p-8 rounded-2xl bg-white space-y-4 hover:shadow transition">
                <h4 class="text-base font-bold text-stone-900 font-sans uppercase">Send Message</h4>
                <x-ui.input label="Name" placeholder="Your full name" />
                <x-ui.input label="Email" type="email" placeholder="Your email address" />
                <div class="w-full">
                    <label class="block text-[10px] uppercase tracking-widest text-stone-500 font-extrabold mb-2">Message</label>
                    <textarea class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="4" placeholder="Your inquiries..."></textarea>
                </div>
                <x-ui.button variant="primary" class="w-full">
                    Send Message
                </x-ui.button>
            </div>
        </div>
    </div>
</section>
