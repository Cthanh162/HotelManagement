<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\StatisticController;
use Illuminate\Support\Facades\Route;

Route::get('/bookings', [BookingController::class, 'getAll']);
Route::get('/bookings/{id}', [BookingController::class, 'get'])->where('id', '[0-9]+');
Route::post('/bookings', [BookingController::class, 'store']);
Route::put('/bookings/{id}', [BookingController::class, 'update'])->where('id', '[0-9]+');
Route::get('/bookings/search', [BookingController::class, 'search']);
// Route::patch('/bookings/{id}/confirm', [BookingController::class, 'confirm']);
// Route::put('/bookings/{bookingId}/approve', [BookingController::class, 'approve']);
Route::post('/bookings/{id}/upload-payment', [BookingController::class, 'uploadPayment'])->where('id', '[0-9]+');
Route::get('/bookings/pending', [BookingController::class, 'getPendingPayments']);
Route::put('/bookings/{id}/approve', [BookingController::class, 'approvePayment'])->where('id', '[0-9]+');
Route::get('/bookings/test', [BookingController::class, 'getTest']);
Route::get('/bookings/user/{userId}', [BookingController::class, 'getByUser'])->where('userId', '[0-9]+');
Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->where('id', '[0-9]+'); 
Route::put('/bookings/{id}/checkout', [BookingController::class, 'checkout'])->where('id', '[0-9]+');
Route::get('/stats/revenue', [StatisticController::class, 'revenue']);
Route::post('/bookings/{id}/calculate-surcharge', [BookingController::class, 'calculateSurcharge'])->where('id', '[0-9]+');
Route::put('/bookings/{id}/confirm-checkout', [BookingController::class, 'confirmCheckout'])->where('id', '[0-9]+');
