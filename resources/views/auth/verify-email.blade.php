<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Verifikasi Email</h2>
        <p class="mt-2 text-sm text-stone-500">Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik link yang baru saja kami kirimkan ke email Anda. Jika tidak menerima email, kami akan mengirimkan ulang.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 font-semibold text-sm text-emerald-600 bg-emerald-50 p-3.5 border border-emerald-200 rounded-xl text-center">
            Link verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
        </div>
    @endif

    <div class="mt-6 flex flex-col space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full justify-center py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg hover:shadow-brand-500/10 transition-all duration-150">
                Kirim Ulang Email Verifikasi
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-sm font-semibold text-stone-600 hover:text-stone-900 hover:underline transition duration-150 text-center focus:outline-none">
                Keluar (Log Out)
            </button>
        </form>
    </div>
</x-guest-layout>
