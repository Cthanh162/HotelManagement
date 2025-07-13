<?php
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/contacts', [ContactController::class, 'store']);
});
