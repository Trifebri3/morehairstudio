<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Lupa Password?</h2>
        <p class="mt-2 text-sm text-stone-500">Jangan khawatir! Beritahu kami alamat email Anda dan kami akan mengirimkan link untuk mereset password Anda.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input id="email" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col space-y-4">
            <x-primary-button class="w-full justify-center py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg hover:shadow-brand-500/10 transition-all duration-150">
                Kirim Link Reset Password
            </x-primary-button>
            
            <div class="text-center">
                <a class="text-sm font-semibold text-brand-600 hover:text-brand-700 hover:underline transition duration-150" href="{{ route('login') }}">
                    Kembali ke Halaman Login
                </a>
            </div>
        </div>
    </form>

    <!-- Developer Assistant (Local Mode Only) -->
    @if (app()->environment('local') && session()->has('password_reset_token_dev') && session()->has('password_reset_email_dev'))
        <div class="mt-8 p-4 bg-amber-50/90 border border-amber-200/80 rounded-xl text-stone-700 text-sm shadow-sm transition-all duration-300">
            <div class="flex items-center space-x-2 text-amber-800 font-bold mb-2">
                <span class="text-base">🔧</span>
                <span>Developer Assistant (Mode Lokal)</span>
            </div>
            <p class="text-stone-600 mb-3 text-xs leading-relaxed">Email tidak dikirim ke internet karena driver mail menggunakan log. Klik tombol di bawah ini untuk menguji reset password langsung:</p>
            <a href="{{ route('password.reset', ['token' => session('password_reset_token_dev'), 'email' => session('password_reset_email_dev')]) }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl text-xs transition duration-150 text-center shadow-md">
                Uji Atur Ulang Password
            </a>
        </div>
    @endif
</x-guest-layout>
