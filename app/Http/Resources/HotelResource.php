<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'Hotel',
    properties: [
        new OAT\Property(property: 'hotelId', type: 'integer', example: 1),
        new OAT\Property(property: 'hotelName', type: 'string', example: 'Luxury Hotel'),
        new OAT\Property(property: 'address', type: 'string', example: '123 Main St'),
        new OAT\Property(property: 'phoneNumber', type: 'string', example: '+123456789'),
        new OAT\Property(property: 'nearbyPlaces', type: 'string', example: 'Beach, Mall'),
    ]
)]
class HotelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'hotelId' => $this->hotelId,
            'hotelName' => $this->hotelName,
            'address' => $this->address,
            'phoneNumber' => $this->phoneNumber,
            'nearbyPlaces' => $this->nearbyPlaces,
        ];
    }
}
