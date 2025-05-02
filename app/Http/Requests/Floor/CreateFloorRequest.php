<?php

namespace App\Http\Requests\Floor;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'CreateFloorRequest',
    required: ['hotelId', 'floorName'],
    properties: [
        new OAT\Property(property: 'hotelId', type: 'integer', example: 1),
        new OAT\Property(property: 'floorName', type: 'string', example: 'Tầng 1'),
    ]
)]
class CreateFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotelId' => 'required|exists:hotels,hotelId',  // Kiểm tra khách sạn tồn tại
            'floorName' => 'required|string|max:255',
        ];
    }
}
