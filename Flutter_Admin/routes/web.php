<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //profiles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //rooms
    Route::get('/rooms/create', [RoomController::class, 'viewCreatePage'])->name('room.create');
    Route::get('/rooms',[RoomController::class, 'index'])->name('room.index_page');
    Route::post('/rooms',[RoomController::class, 'butang'])->name('room.store_data');
    Route::put('/rooms/{room}',[RoomController::class, 'update'])->name('room.update_data');
    //dili mn daw ni gamiton :p
    Route::delete('/rooms/{room}',[RoomController::class, 'destory'])->name('room.delete_data');

});

require __DIR__.'/auth.php';
