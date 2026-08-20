<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight">Daftar Akun</h2>
        <p class="mt-2 text-sm text-stone-500">Silakan daftarkan akun baru Anda</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nama Lengkap" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input wire:model="name" id="name" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" type="text" name="name" required autofocus autocomplete="name" placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input wire:model="email" id="email" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm" type="email" name="email" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input wire:model="password" id="password" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Password" class="text-stone-700 font-semibold mb-1.5" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a class="text-sm font-semibold text-brand-600 hover:text-brand-700 hover:underline transition duration-150" href="{{ route('login') }}" wire:navigate>
                Sudah punya akun?
            </a>

            <x-primary-button class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-md hover:shadow-brand-500/10 transition-all duration-150">
                Daftar
            </x-primary-button>
        </div>
    </form>
</div>
