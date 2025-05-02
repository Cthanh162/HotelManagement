<?php 

use App\Http\Controllers\StatisticController;
use Illuminate\Support\Facades\Route;

Route::prefix('statistics')->group(function () {
    Route::get('/revenue/daily', [StatisticController::class, 'revenueByDay']);
    Route::get('/revenue/monthly', [StatisticController::class, 'revenueByMonth']);
});