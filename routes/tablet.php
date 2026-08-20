<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tablet\Dashboard;
use App\Livewire\Tablet\WalkIn;
use App\Livewire\Tablet\CheckIn;
use App\Livewire\Tablet\Attendance;
use App\Livewire\Tablet\Queue;
use App\Livewire\Tablet\Styscreen;

Route::prefix('tablet')->name('tablet.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/walk-in', WalkIn::class)->name('walk-in');
    Route::get('/check-in', CheckIn::class)->name('check-in');
    Route::get('/attendance', Attendance::class)->name('attendance');
    Route::get('/queue', Queue::class)->name('queue');
    Route::get('/styscreen', Styscreen::class)->name('styscreen');
});
