<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Booking\BookingWizard;

Route::get('/booking', BookingWizard::class)->name('booking.index');
Route::get('/booking/success/{token}', [BookingWizard::class, 'success'])->name('booking.success');
Route::get('/booking/ticket/{code}', [\App\Http\Controllers\BookingTicketController::class, 'show'])->name('booking.ticket');
