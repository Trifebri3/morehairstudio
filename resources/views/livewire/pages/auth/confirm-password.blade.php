<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Konfirmasi Password</h2>
        <p class="mt-2 text-sm text-stone-500">Ini adalah area keamanan terproteksi. Silakan konfirmasi password Anda sebelum melanjutkan.</p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-5">
        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password Anda" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input wire:model="password"
                          id="password"
                          class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm"
                          type="password"
                          name="password"
                          required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg hover:shadow-brand-500/10 transition-all duration-150">
                Konfirmasi
            </x-primary-button>
        </div>
    </form>
</div>
