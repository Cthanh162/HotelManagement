<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'Room',
    properties: [
        new OAT\Property(property: 'roomId', type: 'integer', example: 1),
        new OAT\Property(property: 'roomName', type: 'string', example: 'Deluxe Room'),
        new OAT\Property(property: 'status', type: 'string', example: 'Available'),
        new OAT\Property(property: 'roomType', type: 'string', example: 'Single'),
        new OAT\Property(property: 'capacity', type: 'integer', example: 2),
        new OAT\Property(property: 'price', type: 'number', format: 'float', example: 100.50),
        new OAT\Property(property: 'description', type: 'string', example: 'A luxurious room with sea view'),
        new OAT\Property(property: 'roomImages', type: 'array', items: new OAT\Items(type: 'string'), example: ['images/abc.jpg', 'images/xyz.jpg']),
        new OAT\Property(property: 'roomVideo', type: 'string', example: 'videos/video.mp4'),
        new OAT\Property(property: 'hotelId', type: 'integer', example: 1),
        new OAT\Property(property: 'floorId', type: 'integer', example: 2),
        new OAT\Property(property: 'userId', type: 'integer', example: 3)
    ]
)]
class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'roomId' => $this->roomId,
            'roomName' => $this->roomName,
            'status' => $this->status,
            'roomType' => $this->roomType,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'description' => $this->description,
            'roomImages' => $this->roomImages,
            'roomVideo' => $this->roomVideo,
            'hotelId' => $this->hotelId,
            'floorId' => $this->floorId,
            'userId' => $this->userId,
        ];
    }
}
