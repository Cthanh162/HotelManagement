<?php

use App\Http\Controllers\HotelController;
use Illuminate\Support\Facades\Route;
Route::get('/hotels', [HotelController::class, 'index']);
Route::post('/hotels', [HotelController::class, 'store']);
Route::get('/hotels/{id}', [HotelController::class, 'show']);
Route::put('/hotels/{id}', [HotelController::class, 'update']);
Route::delete('/hotels/{id}', [HotelController::class, 'destroy']);

// Route::post('/signup', [AuthController::class, 'signup']);
