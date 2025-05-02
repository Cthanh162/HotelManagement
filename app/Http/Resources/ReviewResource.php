<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'Review',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'roomId', type: 'integer', example: 5),
        new OAT\Property(property: 'userId', type: 'integer', example: 2),
        new OAT\Property(property: 'rating', type: 'number', format: 'float', example: 4.5),
        new OAT\Property(property: 'des', type: 'string', example: 'Nice and clean room.'),
        new OAT\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-01T10:00:00Z')
    ]
)]
class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'roomId' => $this->roomId,
            'userId' => $this->userId,
            'rating' => $this->rating,
            'des' => $this->des,
            'createdAt' => $this->createdAt,
        ];
    }
}