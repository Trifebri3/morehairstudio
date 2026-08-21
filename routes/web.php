<?php

use Illuminate\Support\Facades\Route;


// Public marketing website
Route::get('/', [\App\Http\Controllers\PublicController::class, 'home'])->name('home');
Route::get('/about', [\App\Http\Controllers\PublicController::class, 'about'])->name('about');
Route::get('/services', [\App\Http\Controllers\PublicController::class, 'services'])->name('services.index');
Route::get('/stylists', [\App\Http\Controllers\PublicController::class, 'stylists'])->name('stylists.index');
Route::get('/outlets', [\App\Http\Controllers\PublicController::class, 'outlets'])->name('outlets.index');
Route::get('/outlets/{slug}', [\App\Http\Controllers\PublicController::class, 'outletShow'])->name('outlets.show');
Route::get('/terms', [\App\Http\Controllers\PublicController::class, 'terms'])->name('terms');
Route::get('/privacy', [\App\Http\Controllers\PublicController::class, 'privacy'])->name('privacy');

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

use App\Http\Controllers\Hairstylis\DashboardController;

Route::middleware(['auth', 'verified'])->prefix('stylist')->name('stylist.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/leave', [DashboardController::class, 'requestLeave'])->name('leave.request');
    Route::post('/activate', [DashboardController::class, 'requestActivate'])->name('activate.request');
    Route::post('/booking/{id}/confirm', [DashboardController::class, 'confirmBooking'])->name('booking.confirm');
    Route::post('/booking/{id}/complete', [DashboardController::class, 'completeBooking'])->name('booking.complete');
});

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordController;

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
});

require __DIR__.'/auth.php';



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
