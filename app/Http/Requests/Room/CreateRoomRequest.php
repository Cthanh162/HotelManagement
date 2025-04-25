<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'CreateRoomRequest',
    required: ['hotelId', 'floorId', 'roomName'],
    properties: [
        new OAT\Property(property: 'hotelId', type: 'integer'),
        new OAT\Property(property: 'floorId', type: 'integer'),
        new OAT\Property(property: 'roomName', type: 'string'),
        new OAT\Property(property: 'status', type: 'string'),
        new OAT\Property(property: 'roomType', type: 'string'),
        new OAT\Property(property: 'capacity', type: 'integer'),
        new OAT\Property(property: 'price', type: 'number'),
        new OAT\Property(property: 'description', type: 'string'),
        new OAT\Property(property: 'roomImages', type: 'array', items: new OAT\Items(type: 'string', format: 'binary')),
        new OAT\Property(property: 'roomVideo', type: 'string', format: 'binary'),
    ]
)]
class CreateRoomRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'hotelId' => 'required|exists:hotels,id',
            'floorId' => 'required|exists:floors,id',
            'roomName' => 'required|string|max:255',

            'status' => 'nullable|string',
            'roomType' => 'nullable|string',
            'capacity' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',

            // ✅ Validate cả mảng file và từng phần tử
            'roomImages.*' => 'file|mimes:jpg,jpeg,png,webp|max:102400',
            'roomImages.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:102400', // 10MB per image

            // ✅ Hỗ trợ video lớn hơn (VD: 50MB)
            'roomVideo' => 'nullable|file|mimes:mp4,mov,avi|max:102400', // 50MB
        ];
    }
}
