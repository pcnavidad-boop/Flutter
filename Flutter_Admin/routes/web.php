<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\ServiceBookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeCheckoutController;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', function () {
    return view('welcome');
});

// Stripe return routes (public)
Route::get('/payment/success', function () {
    return "Payment successful!";
});

Route::get('/payment/cancel', function () {
    return "Payment cancelled.";
});
// Stripe test UI
Route::view('/test-checkout', 'test_checkout');

// Public Stripe Checkout (guest-side)
Route::post('/stripe/checkout', [StripeCheckoutController::class, 'create'])
    ->name('stripe.checkout');

// not auth middleware for dummy frontend
Route::prefix('hotel')->group(function () {
    Route::get('/', fn() => view('customer.landing'))->name('hotel.landing');
    Route::get('/rooms', fn() => view('customer.rooms'))->name('hotel.rooms');
    Route::get('/services', fn() => view('customer.services'))->name('hotel.services');
    Route::get('/book-room', fn() => view('customer.booking-room'))->name('hotel.book.room');
    Route::get('/book-service', fn() => view('customer.booking-service'))->name('hotel.book.service');
});

// admin-side + auth routes

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profiles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rooms
    Route::get('/rooms', [RoomController::class, 'index'])->name('room.index_page');
    Route::post('/rooms', [RoomController::class, 'create'])->name('room.store_data');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('room.update_data');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('room.delete_data');

    // Services
    Route::get('/services', [ServiceController::class, 'index'])->name('service.index_page');
    Route::post('/services', [ServiceController::class, 'create'])->name('service.store_data');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('service.update_data');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('service.delete_data');

    // Room Bookings
    Route::get('/room-bookings/create', [RoomBookingController::class, 'viewCreatePage'])->name('room_booking.create');
    Route::get('/room-bookings', [RoomBookingController::class, 'index'])->name('room_booking.index_page');
    Route::post('/room-bookings', [RoomBookingController::class, 'create'])->name('room_booking.store_data');
    Route::put('/room-bookings/{roomBooking}', [RoomBookingController::class, 'update'])->name('room_booking.update_data');
    Route::delete('/room-bookings/{roomBooking}', [RoomBookingController::class, 'destroy'])->name('room_booking.delete_data');

    // Service Bookings
    Route::get('/service-bookings/create', [ServiceBookingController::class, 'viewCreatePage'])->name('service_booking.create');
    Route::get('/service-bookings', [ServiceBookingController::class, 'index'])->name('service_booking.index_page');
    Route::post('/service-bookings', [ServiceBookingController::class, 'create'])->name('service_booking.store_data');
    Route::put('/service-bookings/{serviceBooking}', [ServiceBookingController::class, 'update'])->name('service_booking.update_data');
    Route::delete('/service-bookings/{serviceBooking}', [ServiceBookingController::class, 'destroy'])->name('service_booking.delete_data');

    // Offline Payments (Polymorphic)
    Route::get('/payments', [PaymentController::class, 'index'])->name('payment.index_page');
    Route::post('/payments', [PaymentController::class, 'create'])->name('payment.store_data');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payment.delete_data');
});

require __DIR__.'/auth.php';
