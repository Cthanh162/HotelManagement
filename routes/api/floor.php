<?php

use App\Http\Controllers\HotelFloorController;
use Illuminate\Support\Facades\Route;

Route::get('floors', [HotelFloorController::class, 'index']);
Route::post('floors', [HotelFloorController::class, 'store']);
Route::post('floors/{id}', [HotelFloorController::class, 'show']);
Route::post('/floors/{id}', [HotelFloorController::class, 'update']);
Route::post('/floors/{id}', [HotelFloorController::class, 'destroy']);
