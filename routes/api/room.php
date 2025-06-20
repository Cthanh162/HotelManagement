<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;

Route::get('/rooms', [RoomController::class, 'index']); // Lấy danh sách phòng
Route::post('/rooms', [RoomController::class, 'store']); // Tạo mới phòng
Route::get('/rooms/{id}', [RoomController::class, 'show']); // Lấy phòng theo ID
Route::put('/rooms/{id}', [RoomController::class, 'update']); // Cập nhật phòng
Route::delete('/rooms/{id}', [RoomController::class, 'destroy']); // Xóa phòng
Route::get('/rooms/search', [RoomController::class, 'search']);
Route::get('/rooms/suggestions', [RoomController::class, 'suggestions']);
Route::get('/rooms/top-rated', [RoomController::class, 'getTopRatedRooms']);
Route::get('/rooms/most-booked', [RoomController::class, 'getMostBookedRooms']);

