<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Public\Home;
use App\Livewire\Public\About;
use App\Livewire\Public\ServicesIndex;
use App\Livewire\Public\StylistsIndex;
use App\Livewire\Public\OutletsIndex;
use App\Livewire\Public\Terms;
use App\Livewire\Public\Privacy;

// Public marketing website
Route::get('/', Home::class)->name('home');
Route::get('/about', About::class)->name('about');
Route::get('/services', ServicesIndex::class)->name('services.index');
Route::get('/stylists', StylistsIndex::class)->name('stylists.index');
Route::get('/outlets', OutletsIndex::class)->name('outlets.index');
Route::get('/outlets/{slug}', \App\Livewire\Public\OutletShow::class)->name('outlets.show');
Route::get('/terms', Terms::class)->name('terms');
Route::get('/privacy', Privacy::class)->name('privacy');

// Scoped Dashboard redirection logic based on user roles
Route::get('dashboard', function () {
    $user = auth()->user();
    if ($user->isSuperAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->isOutletAdmin()) {
        return redirect()->route('outlet.dashboard');
    }
    if ($user->isStylist()) {
        return redirect()->route('stylist.dashboard');
    }
    return redirect()->route('booking.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/stylist/dashboard', \App\Livewire\Hairstylis\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('stylist.dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// Logout route mapping
Route::post('logout', function (\App\Livewire\Actions\Logout $logout) {
    $logout();
    return redirect('/');
})->name('logout');

// Locale switching
Route::get('locale/{locale}', function ($locale) {
    if (in_array($locale, ['id', 'en'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('locale.switch');

// Impersonation Routes
Route::get('/impersonate/start/{id}', function ($id) {
    if (!auth()->user()->isSuperAdmin() && !session()->has('impersonator_id')) {
        abort(403);
    }
    if (!session()->has('impersonator_id')) {
        session(['impersonator_id' => auth()->id()]);
    }
    $userToImpersonate = \App\Models\User::findOrFail($id);
    auth()->login($userToImpersonate);
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('impersonate.start');

Route::get('/impersonate/stop', function () {
    if (!session()->has('impersonator_id')) {
        abort(403);
    }
    $originalUserId = session()->pull('impersonator_id');
    $originalUser = \App\Models\User::findOrFail($originalUserId);
    auth()->login($originalUser);
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('impersonate.stop');
