<style>
/* ── STUDIO INFO FONTS ── */
.si-badge   { font-family: 'Suisse Intl', sans-serif; font-size: 0.7rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600; }
.si-h2      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; }
.si-h3      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; }
.si-body    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
.si-label   { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 400; }
.si-value   { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; }
.si-cta     { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; }
</style>

<section id="studio" class="py-16 md:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-start">

            <!-- Left: Heading + body -->
            <div class="space-y-6">
                <span class="si-badge text-[#c9512d]">Hubungi Kami</span>
                <h2 class="si-h2 text-2xl sm:text-3xl md:text-4xl text-stone-900">
                    Studio &amp; Informasi<br>Kontak
                </h2>
                <p class="si-body text-stone-600 text-sm sm:text-base leading-relaxed">
                    Pertanyaan tentang layanan, jadwal, atau kolaborasi? Tim kami siap membantu. Kunjungi studio kami langsung atau hubungi melalui saluran di bawah ini.
                </p>

                <!-- Map embed placeholder -->
                <div class="rounded-2xl overflow-hidden border border-stone-200 bg-stone-100 h-48">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.8!2d107.6133521!3d-6.925552!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e7b922abb217%3A0xa139ae533e5eeeb8!2sMore%20Hair%20Studio!5e0!3m2!1sen!2sid!4v1"
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" title="MORE Hair Studio Location">
                    </iframe>
                </div>
            </div>

            <!-- Right: Info cards -->
            <div class="space-y-5">

                <div class="p-6 rounded-2xl border border-stone-200 space-y-4">
                    <h3 class="si-h3 text-base text-stone-900">MORE Hair Studio</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="si-label text-stone-400 block mb-0.5">Alamat</span>
                            <span class="si-value text-sm text-stone-700 leading-relaxed block">Jl. Sastimatmaja No.6, Paledang, Kec. Lengkong,<br>Kota Bandung, Jawa Barat 40261</span>
                        </div>
                        <div>
                            <span class="si-label text-stone-400 block mb-0.5">Jam Operasional</span>
                            <span class="si-h3 text-sm text-stone-900 block">10:00 – 20:00 WIB</span>
                            <span class="si-body text-xs text-stone-500">Senin – Minggu (termasuk hari libur)</span>
                        </div>
                        <div>
                            <span class="si-label text-stone-400 block mb-0.5">WhatsApp</span>
                            <a href="https://wa.me/6281234567890" class="si-h3 text-sm text-[#c9512d] hover:underline">+62 812-3456-7890</a>
                        </div>
                        <div>
                            <span class="si-label text-stone-400 block mb-0.5">Instagram</span>
                            <a href="https://instagram.com/morehairstudio" class="si-h3 text-sm text-[#c9512d] hover:underline">@morehairstudio</a>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('booking.index') }}"
                       class="si-cta flex-1 py-4 bg-[#c9512d] hover:bg-[#b74423] text-white text-sm text-center uppercase tracking-wider rounded-xl transition duration-200">
                        Booking Sekarang
                    </a>
                    <a href="https://wa.me/6281234567890"
                       class="si-cta flex-1 py-4 border border-stone-200 hover:border-[#c9512d] hover:text-[#c9512d] text-stone-700 text-sm text-center uppercase tracking-wider rounded-xl transition duration-200">
                        WhatsApp
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
