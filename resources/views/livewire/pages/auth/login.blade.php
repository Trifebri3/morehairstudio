<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Selamat Datang</h2>
        <p class="mt-2 text-sm text-stone-500">Silakan masuk ke akun Anda</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input wire:model="form.email" id="email" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" type="email" name="email" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input wire:model="form.password" id="password" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-stone-300 text-brand-600 shadow-sm focus:ring-brand-500 cursor-pointer" name="remember">
                <span class="ms-2 text-sm text-stone-600 cursor-pointer select-none">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-brand-600 hover:text-brand-700 hover:underline transition duration-150" href="{{ route('password.request') }}" wire:navigate>
                    Lupa Password?
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg hover:shadow-brand-500/10 transition-all duration-150">
                Masuk
            </x-primary-button>
        </div>
    </form>
</div>
