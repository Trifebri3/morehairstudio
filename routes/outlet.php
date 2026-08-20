<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Outlet\Dashboard;
use App\Livewire\Outlet\Bookings;
use App\Livewire\Outlet\Stylists;
use App\Livewire\Outlet\Attendance;

Route::prefix('outlet')->name('outlet.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/bookings', Bookings::class)->name('bookings');
    Route::get('/stylists', Stylists::class)->name('stylists');
    Route::get('/attendance', Attendance::class)->name('attendance');
    Route::get('/pos', \App\Livewire\Admin\Pos::class)->name('pos');
    Route::get('/transactions', \App\Livewire\Admin\Transactions::class)->name('transactions');
});
