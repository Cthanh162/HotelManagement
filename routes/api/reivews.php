<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
Route::get('/reviews', [ReviewController::class, 'index']);
Route::post('/reviews', [ReviewController::class, 'store']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);
Route::put('/reviews/{id}', [ReviewController::class, 'update']);
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

// Route::post('/signup', [AuthController::class, 'signup']);
