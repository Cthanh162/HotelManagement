<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'Floor',
    required: ['hotelId', 'floorName'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'hotelId', type: 'integer', example: 2),
        new OAT\Property(property: 'floorName', type: 'string', example: 'Tầng 2'),
    ]
)]
class FloorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'hotelId' => $this->hotelId,
            'floorName' => $this->floorName,
        ];
    }
}
