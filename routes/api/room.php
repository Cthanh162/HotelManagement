<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;

Route::get('/rooms', [RoomController::class, 'index']); // Lấy danh sách phòng
Route::get('/rooms/getAll', [RoomController::class, 'getAll']); // Lấy danh sách phòng
Route::post('/rooms', [RoomController::class, 'store']); // Tạo mới phòng
Route::get('/rooms/{id}', [RoomController::class, 'show'])->where('id', '[0-9]+');; // Lấy phòng theo ID
Route::put('/rooms/{id}', [RoomController::class, 'update'])->where('id', '[0-9]+');; // Cập nhật phòng
Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->where('id', '[0-9]+');; // Xóa phòng
Route::get('/rooms/search', [RoomController::class, 'search']);
Route::get('/rooms/suggestions', [RoomController::class, 'suggestions']);
// Route::get('/rooms/available', [RoomController::class, 'getAvailableRooms']);
Route::get('/rooms/available', [RoomController::class, 'searchAvailable']);
Route::get('/rooms/top-rated', [RoomController::class, 'getTopRatedRooms']);
Route::get('/rooms/most-booked', [RoomController::class, 'getMostBookedRooms']); 
Route::get('/rooms/{id}/services', [RoomController::class, 'getServices']);