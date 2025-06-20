<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;

// Public routes here

Route::middleware(['auth:user', 'admin'])->group(function () {
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms/{id}', [RoomController::class, 'update']);
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);
    Route::get('/admin/bookings', [BookingController::class, 'adminIndex']);
    Route::post('/bookings/{id}/approve', [BookingController::class, 'approve']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    // Add other admin-only routes here
});
