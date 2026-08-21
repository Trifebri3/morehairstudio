<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Outlet\OutletDashboardController;

Route::prefix('outlet')->name('outlet.')->group(function () {
    Route::get('/dashboard', [OutletDashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/dashboard/settings', [OutletDashboardController::class, 'saveSettings'])->name('settings.save');
    
    Route::get('/bookings', [OutletDashboardController::class, 'bookings'])->name('bookings');
    Route::post('/bookings/{id}/status', [OutletDashboardController::class, 'updateBookingStatus'])->name('bookings.status');
    
    Route::get('/stylists', [OutletDashboardController::class, 'stylists'])->name('stylists');
    Route::post('/stylists/{id}/toggle', [OutletDashboardController::class, 'toggleStylistStatus'])->name('stylists.toggle');
    Route::post('/stylists/{id}/approve', [OutletDashboardController::class, 'approveStylistStatus'])->name('stylists.approve');
    Route::post('/stylists/{id}/reject', [OutletDashboardController::class, 'rejectStylistStatus'])->name('stylists.reject');
    
    Route::get('/attendance', [OutletDashboardController::class, 'attendance'])->name('attendance');
    
    // POS routes aligned to traditional admin controllers
    Route::get('/pos', [\App\Http\Controllers\Admin\PosController::class, 'index'])->name('pos');
    Route::get('/transactions', [\App\Http\Controllers\Admin\AdminPanelController::class, 'transactions'])->name('transactions');
});
