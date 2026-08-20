<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
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
        <x-primary-button wire:click="sendVerification" class="w-full justify-center py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg hover:shadow-brand-500/10 transition-all duration-150">
            Kirim Ulang Email Verifikasi
        </x-primary-button>

        <button wire:click="logout" type="button" class="text-sm font-semibold text-stone-600 hover:text-stone-900 hover:underline transition duration-150 text-center">
            Keluar (Log Out)
        </button>
    </div>
</div>
