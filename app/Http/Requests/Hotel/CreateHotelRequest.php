<?php

namespace App\Http\Requests\Hotel;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'CreateHotelRequest',
    required: ['hotelName', 'address'],
    properties: [
        new OAT\Property(property: 'hotelName', type: 'string', example: 'Luxury Hotel'),
        new OAT\Property(property: 'address', type: 'string', example: '123 Main St'),
        new OAT\Property(property: 'phoneNumber', type: 'string', example: '+123456789'),
        new OAT\Property(property: 'nearbyPlaces', type: 'string', example: 'Beach, Mall'),
    ]
)]
class CreateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotelName' => 'required|string|max:255',
            'address' => 'required|string',
            'phoneNumber' => 'nullable|string',
            'nearbyPlaces' => 'nullable|string',
        ];
    }
}
