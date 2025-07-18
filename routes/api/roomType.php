<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomTypeController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('room-types')->group(function () {
        Route::get('/', [RoomTypeController::class, 'index']);
        Route::get('/{id}', [RoomTypeController::class, 'show']);
        Route::post('/', [RoomTypeController::class, 'store']);
        Route::put('/{id}', [RoomTypeController::class, 'update']);
        Route::delete('/{id}', [RoomTypeController::class, 'destroy']);
    });
});
Route::prefix('room-types')->group(function () {
        Route::get('/', [RoomTypeController::class, 'index']);
        Route::get('/{id}', [RoomTypeController::class, 'show']);
       
    });
