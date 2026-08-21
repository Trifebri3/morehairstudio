<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Atur Ulang Password</h2>
        <p class="mt-2 text-sm text-stone-500">Silakan masukkan password baru Anda</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input id="email" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password Baru" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input id="password" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Password Baru" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input id="password_confirmation" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm"
                          type="password"
                          name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg hover:shadow-brand-500/10 transition-all duration-150">
                Atur Ulang Password
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
