<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/getAll', [RoomController::class, 'getAll']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{id}', [RoomController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/rooms/{id}', [RoomController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->where('id', '[0-9]+');
    Route::get('/rooms/search', [RoomController::class, 'search']);
    Route::get('/rooms/suggestions', [RoomController::class, 'suggestions']);
    Route::get('/rooms/available', [RoomController::class, 'searchAvailable']);
    Route::get('/rooms/top-rated', [RoomController::class, 'getTopRatedRooms']);
    Route::get('/rooms/most-booked', [RoomController::class, 'getMostBookedRooms']);
    Route::get('/rooms/{id}/services', [RoomController::class, 'getServices'])->where('id', '[0-9]+');
});
