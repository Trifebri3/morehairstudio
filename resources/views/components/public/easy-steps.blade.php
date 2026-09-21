<style>
/* ── EASY STEPS FONTS ── */
.es-badge  { font-family: 'Suisse Intl', sans-serif; font-size: 0.7rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600; }
.es-h2     { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; }
.es-sub    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
.es-num    { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; }
.es-step   { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; }
.es-desc   { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; }
</style>

<section class="py-16 md:py-20 bg-[#fafaf9] border-b border-stone-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <div class="text-center space-y-3">
            <span class="es-badge text-[#c9512d]">Cara Kerja</span>
            <h2 class="es-h2 text-2xl sm:text-3xl md:text-4xl text-stone-900">
                3 Langkah Booking Online
            </h2>
            <p class="es-sub text-sm text-stone-500 max-w-lg mx-auto">
                Reservasi slot dalam hitungan menit. Tanpa antri, tanpa telepon.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">

            <div class="flex flex-col items-start gap-4">
                <div class="es-num w-12 h-12 rounded-2xl bg-[#c9512d] text-white text-xl flex items-center justify-center">
                    01
                </div>
                <div>
                    <h3 class="es-step text-base text-stone-900 mb-1">Pilih Layanan &amp; Barber</h3>
                    <p class="es-desc text-sm text-stone-500 leading-relaxed">Temukan layanan yang sesuai dan pilih hair artist favoritmu dari daftar tersedia.</p>
                </div>
            </div>

            <div class="flex flex-col items-start gap-4">
                <div class="es-num w-12 h-12 rounded-2xl bg-[#c9512d] text-white text-xl flex items-center justify-center">
                    02
                </div>
                <div>
                    <h3 class="es-step text-base text-stone-900 mb-1">Tentukan Jam Kunjungan</h3>
                    <p class="es-desc text-sm text-stone-500 leading-relaxed">Pilih slot waktu yang tersedia secara real-time. Tidak perlu khawatir soal antrian.</p>
                </div>
            </div>

            <div class="flex flex-col items-start gap-4">
                <div class="es-num w-12 h-12 rounded-2xl bg-[#c9512d] text-white text-xl flex items-center justify-center">
                    03
                </div>
                <div>
                    <h3 class="es-step text-base text-stone-900 mb-1">Konfirmasi E-Ticket Instan</h3>
                    <p class="es-desc text-sm text-stone-500 leading-relaxed">Terima tiket digital langsung setelah booking. Tunjukkan saat tiba di studio.</p>
                </div>
            </div>

        </div>

        <div class="text-center">
            <a href="{{ route('booking.index') }}"
               class="es-step inline-flex items-center gap-2 px-8 py-4 bg-stone-900 hover:bg-[#c9512d] text-white text-sm uppercase tracking-wider rounded-xl transition duration-200">
                <span>Booking Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

    </div>
</section>
