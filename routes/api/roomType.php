<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomTypeController;

Route::prefix('room-types')->group(function () {
    Route::get('/', [RoomTypeController::class, 'index']);       // GET all
    Route::get('/{id}', [RoomTypeController::class, 'show']);    // GET one
    Route::post('/', [RoomTypeController::class, 'store']);      // CREATE
    Route::put('/{id}', [RoomTypeController::class, 'update']);  // UPDATE
    Route::delete('/{id}', [RoomTypeController::class, 'destroy']); // DELETE
});