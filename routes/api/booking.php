<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/bookings', [BookingController::class, 'getAll']);
Route::get('/bookings/{id}', [BookingController::class, 'get']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::put('/bookings/{id}', [BookingController::class, 'update']);
Route::get('/bookings/search', [BookingController::class, 'search']);
Route::patch('/bookings/{id}/confirm', [BookingController::class, 'confirm']);

