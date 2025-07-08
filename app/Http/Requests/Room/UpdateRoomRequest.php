<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
class UpdateRoomRequest extends FormRequest
{
    public function rules(): array
    {
        return [
        'hotelId' => 'sometimes|integer|exists:hotels,hotelId',
        'floorId' => 'sometimes|integer|exists:floors,id',
        'roomName' => 'sometimes|string|max:255',
        'status' => 'sometimes|string|in:available,Booked,Maintenance,pending_payment',
        'roomTypeId' => 'sometimes|integer|exists:room_types,id',
        'capacity' => 'sometimes|integer|min:1',
        'adults' => 'sometimes|integer|min:0',
        'children' => 'sometimes|integer|min:0',
        'price' => 'sometimes|numeric|min:0',
        'description' => 'nullable|string',
        'services' => 'sometimes|array',
        'services.*' => 'integer|exists:services,id',
        'roomImages' => 'sometimes|array',
        'roomImages.*' => 'file|image|max:2048',
        'roomVideo' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:51200', // max 50MB
    ];
    }
}
