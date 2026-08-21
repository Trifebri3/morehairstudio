<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tablet\TabletKioskController;

Route::prefix('tablet')->name('tablet.')->group(function () {
    Route::get('/', [TabletKioskController::class, 'dashboard'])->name('dashboard');
    
    // Redirect walk-in to Booking Wizard with walk_in parameters
    Route::get('/walk-in', function () {
        return redirect()->route('booking', [
            'walk_in' => 1,
            'outlet_id' => session('tablet_outlet_id', 1)
        ]);
    })->name('walk-in');

    Route::get('/check-in', [TabletKioskController::class, 'checkIn'])->name('check-in');
    Route::post('/check-in/{id}', [TabletKioskController::class, 'processCheckIn'])->name('check-in.process');

    Route::get('/attendance', [TabletKioskController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/{id}/clock-in', [TabletKioskController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/{id}/clock-out', [TabletKioskController::class, 'clockOut'])->name('attendance.clock-out');

    Route::get('/queue', [TabletKioskController::class, 'queue'])->name('queue');
    Route::post('/queue/{id}/start', [TabletKioskController::class, 'startService'])->name('queue.start');
    Route::post('/queue/{id}/complete', [TabletKioskController::class, 'completeService'])->name('queue.complete');

    Route::get('/styscreen', [TabletKioskController::class, 'styscreen'])->name('styscreen');
    Route::post('/styscreen/login', [TabletKioskController::class, 'styscreenLogin'])->name('styscreen.login');
    Route::post('/styscreen/logout', [TabletKioskController::class, 'styscreenLogout'])->name('styscreen.logout');
    Route::post('/styscreen/{id}/pay', [TabletKioskController::class, 'styscreenPay'])->name('styscreen.pay');
});
