<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'getAll']);
Route::get('{id}', [BookingController::class, 'get']);
Route::post('/', [BookingController::class, 'create']);
Route::put('{id}', [BookingController::class, 'update']);
Route::get('search', [BookingController::class, 'search']);

