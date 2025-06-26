<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
class UpdateRoomRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'hotelId' => 'nullable|integer',
'floorId' => 'nullable|integer',
'roomName' => 'nullable|string|max:255',
            'status' => 'nullable|string',
            'roomTypeId' => 'nullable|string',
            'capacity' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'roomImages' => 'nullable|array',
            'roomImages.*' => 'file|mimes:jpeg,png,jpg|max:10240',
            'roomVideo' => 'nullable|file|mimes:mp4,avi,mov|max:204800',
            'services' => 'nullable|array',
            'services.*' => 'integer|exists:service,id',
        ];
    }
}
