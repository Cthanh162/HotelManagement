<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/services', [ServiceController::class, 'index']);
    Route::post('/services', [ServiceController::class, 'store']);
    Route::get('/services/{id}', [ServiceController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->where('id', '[0-9]+');
});



