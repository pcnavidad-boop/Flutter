<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profiles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rooms
    Route::get('/rooms/create', [RoomController::class, 'viewCreatePage'])->name('room.create');
    Route::get('/rooms', [RoomController::class, 'index'])->name('room.index_page');
    Route::post('/rooms', [RoomController::class, 'create'])->name('room.store_data');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('room.update_data');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('room.delete_data');

    // Services
    Route::get('/services/create', [ServiceController::class, 'viewCreatePage'])->name('service.create');
    Route::get('/services', [ServiceController::class, 'index'])->name('service.index_page');
    Route::post('/services', [ServiceController::class, 'create'])->name('service.store_data');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('service.update_data');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('service.delete_data');

});

require __DIR__.'/auth.php';
