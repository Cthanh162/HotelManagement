<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoomTypeController extends Controller
{
    // Lấy danh sách RoomType
    public function index(): JsonResponse
    {
        return response()->json(['data' => RoomType::all()], 200);
    }

    // Lấy 1 RoomType theo ID
    public function show($id): JsonResponse
    {
        $roomType = RoomType::find($id);

        if (!$roomType) {
            return response()->json(['message' => 'RoomType not found.'], 404);
        }

        return response()->json(['data' => $roomType], 200);
    }

    // Thêm mới RoomType
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'des' => 'nullable|string',
        ]);

        $roomType = RoomType::create($validated);

        return response()->json(['message' => 'Created successfully.', 'data' => $roomType], 201);
    }

    // Cập nhật RoomType
    public function update(Request $request, $id): JsonResponse
    {
        $roomType = RoomType::find($id);

        if (!$roomType) {
            return response()->json(['message' => 'RoomType not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'des' => 'nullable|string',
        ]);

        $roomType->update($validated);

        return response()->json(['message' => 'Updated successfully.', 'data' => $roomType], 200);
    }

    // Xoá RoomType
    public function destroy($id): JsonResponse
    {
        $roomType = RoomType::find($id);

        if (!$roomType) {
            return response()->json(['message' => 'RoomType not found.'], 404);
        }

        $roomType->delete();

        return response()->json(['message' => 'Deleted successfully.'], 200);
    }
}