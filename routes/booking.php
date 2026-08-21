<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Booking\BookingController;

Route::get('/booking', [BookingController::class, 'index'])->name('booking');
Route::get('/booking/index', [BookingController::class, 'index'])->name('booking.index');
Route::get('/booking/slots', [BookingController::class, 'getSlots'])->name('booking.slots');
Route::get('/booking/customer-lookup', [BookingController::class, 'lookupCustomer'])->name('booking.customer-lookup');
Route::get('/booking/apply-promo', [BookingController::class, 'applyPromo'])->name('booking.apply-promo');
Route::post('/booking/confirm', [BookingController::class, 'confirmBooking'])->name('booking.confirm');
Route::get('/booking/success/{token}', [BookingController::class, 'success'])->name('booking.success');
Route::get('/booking/ticket/{code}', [\App\Http\Controllers\BookingTicketController::class, 'show'])->name('booking.ticket');
