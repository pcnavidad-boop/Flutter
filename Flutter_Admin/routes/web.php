<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Customer\RoomController;
use App\Http\Controllers\Customer\ServiceController;
use App\Http\Controllers\Customer\RoomBookingController;
use App\Http\Controllers\Customer\ServiceBookingController;
use App\Http\Controllers\Customer\StripeCheckoutController;

/**
 * PUBLIC LANDING PAGE
 */
Route::get('/', fn() => view('welcome'));

/**
 * CUSTOMER-FACING HOTEL ROUTES
 */
Route::prefix('hotel')->group(function () {

    Route::get('/', fn() => view('customer.landing'))
        ->name('hotel.landing');

    /**
     * ROOMS
     */
    Route::get('/rooms', [RoomController::class, 'index'])
        ->name('hotel.rooms');

    Route::get('/rooms/{room}', [RoomController::class, 'show'])
        ->name('hotel.room.show');

    /**
     * BOOK ROOM
     */
    Route::get('/book-room/{room}', [RoomBookingController::class, 'createPage'])
        ->name('hotel.book.room');

    Route::post('/book-room', [RoomBookingController::class, 'store'])
        ->name('hotel.book.room.store');

    Route::get('/booking/room/{reference}',
        [RoomBookingController::class, 'summary'])
        ->name('hotel.booking.room.summary');

    /**
     * SERVICES
     */
    Route::get('/services', [ServiceController::class, 'index'])
        ->name('hotel.services');

    Route::get('/services/{service}', [ServiceController::class, 'show'])
        ->name('hotel.service.show');

    /**
     * BOOK SERVICE
     */
    Route::get('/book-service/{service}', [ServiceBookingController::class, 'createPage'])
        ->name('hotel.book.service');

    Route::post('/book-service', [ServiceBookingController::class, 'store'])
        ->name('hotel.book.service.store');

    Route::get('/booking/service/{reference}',
        [ServiceBookingController::class, 'summary'])
        ->name('hotel.booking.service.summary');
});

/**
 * STRIPE RETURN ROUTES
 */
Route::get('/payment/success', [StripeCheckoutController::class, 'success'])
    ->name('payment.success');

Route::get('/payment/cancel', [StripeCheckoutController::class, 'cancel'])
    ->name('payment.cancel');

/**
 * STRIPE PAYMENT ROUTES
 */
Route::get('/hotel/pay/downpayment/{reference}',
    [StripeCheckoutController::class, 'payDownpayment'])
    ->name('guest.pay.downpayment');

Route::get('/hotel/pay/full/{reference}',
    [StripeCheckoutController::class, 'payFull'])
    ->name('guest.pay.full');

Route::get('/hotel/pay/remaining/{reference}',
    [StripeCheckoutController::class, 'payRemaining'])
    ->name('guest.pay.remaining');

// ADMIN DASHBOARD REDIRECT
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->name('dashboard');

/**
 * AUTH
 */
require __DIR__ . '/auth.php';
