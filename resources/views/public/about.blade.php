@extends('layouts.public')

@section('title', 'Tentang Kami | MORE Hair Studio Bandung')
@section('meta_description', 'Profil dan filosofi MORE Hair Studio Bandung. Studio perawatan rambut urban dengan pendekatan Human-Hair Centered Design dan sesi konsultasi 10-Menit Define Session.')

@section('content')

<style>
/* ── ABOUT PAGE FONTS ── */
.ab-badge   { font-family: 'SuisseIntlMono', monospace !important; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 400; }
.ab-h1      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.05; letter-spacing: -0.03em; }
.ab-h2      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; line-height: 1.1; letter-spacing: -0.025em; }
.ab-h3      { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; line-height: 1.2; }
.ab-letter  { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 800; }
.ab-body    { font-family: 'Suisse Intl', sans-serif !important; font-weight: 400; line-height: 1.7; }
.ab-sub     { font-family: 'Suisse Intl', sans-serif !important; font-weight: 300; line-height: 1.7; }
.ab-btn     { font-family: 'Stack Sans Notch', sans-serif !important; font-weight: 700; letter-spacing: 0.05em; }
</style>

<div class="bg-white">

    <!-- ─── HERO ─── -->
    <section class="py-20 md:py-28 border-b border-stone-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <span class="ab-badge text-[#c9512d] block">Tentang MORE Hair Studio</span>
            <h1 class="ab-h1 text-4xl sm:text-5xl md:text-6xl lg:text-7xl text-stone-900">
                Ruang Tanpa Batas untuk<br>
                <span class="text-[#c9512d]">Karakter Autentik</span> Anda
            </h1>
            <p class="ab-sub text-stone-600 text-sm sm:text-base md:text-lg max-w-2xl mx-auto">
                Didirikan di jantung kultur urban Kota Bandung, MORE Hair Studio hadir untuk mengembalikan esensi grooming kepada manusia dan karakter unik yang dibawanya.
            </p>
        </div>
    </section>

    <!-- ─── STORY & PHILOSOPHY ─── -->
    <section class="py-16 md:py-20 border-b border-stone-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                <div class="lg:col-span-6 rounded-2xl overflow-hidden border border-stone-200 shadow-sm">
                    <img src="/images/more_studio_interior.jpg" alt="MORE Hair Studio Bandung" class="w-full aspect-4/3 object-cover">
                </div>

                <div class="lg:col-span-6 space-y-5">
                    <span class="ab-badge text-[#c9512d]">Filosofi Kami</span>
                    <h2 class="ab-h2 text-2xl sm:text-3xl md:text-4xl text-stone-900">
                        Human-Hair<br>Centered Design
                    </h2>
                    <p class="ab-body text-stone-600 text-sm">
                        Di tengah industri yang kerap terjebak pada tren seragam dan cetakan pola yang kaku, kami memandang setiap helai, lekuk, dan tekstur rambut sebagai kanvas personal yang membawa cerita unik.
                    </p>
                    <p class="ab-body text-stone-600 text-sm">
                        Melalui pendekatan yang berpusat pada manusia dan rambut, kami tidak memaksakan gaya yang sedang viral jika tidak cocok dengan anatomi wajah atau gaya hidup Anda. Kami berdiskusi, memahami, dan merancang gaya presisi yang nyaman dirawat dalam keseharian.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ─── 4 CORE VALUES ─── -->
    <section class="py-16 md:py-20 bg-[#fafaf9] border-b border-stone-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <div class="text-center max-w-xl mx-auto space-y-3">
                <span class="ab-badge text-[#c9512d]">Prinsip Berkarya</span>
                <h2 class="ab-h2 text-2xl sm:text-3xl md:text-4xl text-stone-900">
                    Nilai yang Menuntun Kami
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white border border-stone-200 rounded-2xl p-6 space-y-3 hover:border-[#c9512d]/40 transition duration-200">
                    <span class="ab-letter text-2xl text-[#c9512d] block">M</span>
                    <h3 class="ab-h3 text-base text-stone-900">Meaningful Expression</h3>
                    <p class="ab-sub text-xs text-stone-500 leading-relaxed">
                        Rambut adalah pernyataan diri. Kami hadir untuk membantu Anda menemukan identitas autentik yang mendefinisikan siapa Anda.
                    </p>
                </div>

                <div class="bg-white border border-stone-200 rounded-2xl p-6 space-y-3 hover:border-[#c9512d]/40 transition duration-200">
                    <span class="ab-letter text-2xl text-[#c9512d] block">O</span>
                    <h3 class="ab-h3 text-base text-stone-900">Open Ecosystem</h3>
                    <p class="ab-sub text-xs text-stone-500 leading-relaxed">
                        Suaka yang terbuka dan inklusif bagi setiap ide, eksplorasi gaya, dan latar belakang individu untuk dirayakan dengan hangat.
                    </p>
                </div>

                <div class="bg-white border border-stone-200 rounded-2xl p-6 space-y-3 hover:border-[#c9512d]/40 transition duration-200">
                    <span class="ab-letter text-2xl text-[#c9512d] block">R</span>
                    <h3 class="ab-h3 text-base text-stone-900">Refinement &amp; Respect</h3>
                    <p class="ab-sub text-xs text-stone-500 leading-relaxed">
                        Dedikasi tinggi pada ketelitian detail teknik potong presisi dan standar pelayanan terbaik bagi setiap pelanggan.
                    </p>
                </div>

                <div class="bg-white border border-stone-200 rounded-2xl p-6 space-y-3 hover:border-[#c9512d]/40 transition duration-200">
                    <span class="ab-letter text-2xl text-[#c9512d] block">E</span>
                    <h3 class="ab-h3 text-base text-stone-900">Evolution</h3>
                    <p class="ab-sub text-xs text-stone-500 leading-relaxed">
                        Terus berinovasi dan berevolusi mengikuti dinamika tren serta kebutuhan personal setiap klien yang terus berkembang.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ─── DEFINE SESSION ─── -->
    <section class="py-16 md:py-20 border-b border-stone-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                <div class="lg:col-span-6 space-y-5 order-2 lg:order-1">
                    <span class="ab-badge text-[#c9512d]">Standar Pelayanan</span>
                    <h2 class="ab-h2 text-2xl sm:text-3xl md:text-4xl text-stone-900">
                        Konsultasi 10-Menit<br>Define Session
                    </h2>
                    <p class="ab-body text-stone-600 text-sm">
                        Sebelum proses pemotongan dimulai, hair artist kami akan duduk bersama Anda selama 10 menit untuk membaca proporsi rahang, arah tumbuh rambut, serta rutinitas styling Anda.
                    </p>
                    <p class="ab-body text-stone-600 text-sm">
                        Hasilnya adalah potongan rambut presisi yang tidak hanya tampak bagus saat Anda keluar dari studio, tetapi juga mudah ditata sendiri di rumah setiap hari.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('booking.index') }}"
                           class="ab-btn inline-flex items-center gap-2 px-6 py-3.5 bg-[#c9512d] hover:bg-[#b74423] text-white text-xs uppercase tracking-widest rounded-xl transition shadow-sm">
                            <span>Reservasi Jadwal</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-6 rounded-2xl overflow-hidden border border-stone-200 shadow-sm order-1 lg:order-2">
                    <img src="/images/more_candid_haircut.jpg" alt="Sesi Grooming di MORE Hair Studio" class="w-full aspect-4/3 object-cover">
                </div>

            </div>
        </div>
    </section>

</div>
@endsection
