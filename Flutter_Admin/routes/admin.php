<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\RoomBookingController;
use App\Http\Controllers\Admin\ServiceBookingController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfileController;

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->group(function () {

        /**
         * DASHBOARD
         */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');

        /**
         * PROFILE
         */
        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('admin.profile.edit');

        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('admin.profile.update');

        Route::delete('/profile', [ProfileController::class, 'destroy'])
            ->name('admin.profile.destroy');

        /**
         * ROOMS
         */
        Route::get('/rooms', [RoomController::class, 'index'])
            ->name('admin.rooms.index');

        Route::post('/rooms', [RoomController::class, 'store'])
            ->name('admin.rooms.store');

        Route::get('/rooms/{room}', [RoomController::class, 'show'])
            ->name('admin.rooms.show');  

        Route::put('/rooms/{room}', [RoomController::class, 'update'])
            ->name('admin.rooms.update');

        Route::patch('/rooms/{room}/archive', [RoomController::class, 'archive'])
            ->name('admin.rooms.archive');

        /**
         * SERVICES
         */
        Route::get('/services', [ServiceController::class, 'index'])
            ->name('admin.services.index');

        Route::post('/services', [ServiceController::class, 'store'])
            ->name('admin.services.store');

        Route::get('/services/{service}', [ServiceController::class, 'show'])
            ->name('admin.services.show');

        Route::put('/services/{service}', [ServiceController::class, 'update'])
            ->name('admin.services.update');

        Route::patch('/services/{service}/archive', [ServiceController::class, 'archive'])
            ->name('admin.services.archive');

        /**
         * ROOM BOOKINGS
         */
        Route::get('/room-bookings', [RoomBookingController::class, 'index'])
            ->name('admin.room_bookings.index');

        Route::post('/room-bookings', [RoomBookingController::class, 'store'])
            ->name('admin.room_bookings.store');

        Route::get('/room-bookings/{booking}', [RoomBookingController::class, 'show'])
            ->name('admin.room_bookings.show');

        Route::put('/room-bookings/{booking}', [RoomBookingController::class, 'update'])
            ->name('admin.room_bookings.update');

        Route::patch('/room-bookings/{booking}/cancel', [RoomBookingController::class, 'cancel']
            )->name('admin.room_bookings.cancel');

        Route::get('/room-bookings/check-availability/{room}', [RoomBookingController::class, 'checkAvailability']
            )->name('admin.room_bookings.checkAvailability');

        /**
         * SERVICE BOOKINGS
         */
        Route::get('/service-bookings', [ServiceBookingController::class, 'index'])
            ->name('admin.service_bookings.index');

        Route::post('/service-bookings', [ServiceBookingController::class, 'store'])
            ->name('admin.service_bookings.store');

        Route::get('/service-bookings/{booking}', [ServiceBookingController::class, 'show'])
            ->name('admin.service_bookings.show');

        Route::put('/service-bookings/{booking}', [ServiceBookingController::class, 'update'])
            ->name('admin.service_bookings.update');

        Route::patch('/service-bookings/{booking}/cancel', [ServiceBookingController::class, 'cancel']
            )->name('admin.service_bookings.cancel');

        Route::get('/service-bookings/check-availability/{service}', [ServiceBookingController::class, 'checkAvailability']
            )->name('admin.service_bookings.checkAvailability');

        /**
         * PAYMENTS 
         */
        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('admin.payments.index');

        Route::post('/payments', [PaymentController::class, 'store'])
            ->name('admin.payments.store');

        Route::get('/payments/{payment}', [PaymentController::class, 'show'])
            ->name('admin.payments.show');

        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])
            ->name('admin.payments.destroy');

        Route::get('/payments/bookings/{type}', [PaymentController::class, 'listBookings']);

        Route::get('/payments/summary/{type}/{reference}', [PaymentController::class, 'bookingSummary']);

        /**
         * NOTIFICATIONS
         * Dropdown-only (like Facebook)
         */
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('admin.notifications.index');

        Route::get('/notifications/open/{id}', [NotificationController::class, 'open'])
            ->name('admin.notifications.open');

        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
            ->name('admin.notifications.destroy');

        /**
         * AJAX routes for bell icon
         */
        Route::get('/notifications/fetch-unread', [NotificationController::class, 'fetchUnread'])
            ->name('admin.notifications.fetchUnread');

        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])
            ->name('admin.notifications.markAsRead');

        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('admin.notifications.markAllAsRead');
    });
