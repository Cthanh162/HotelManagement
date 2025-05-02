<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'RevenueStat',
    properties: [
        new OAT\Property(property: 'hotelId', type: 'integer', example: 1),
        new OAT\Property(property: 'hotelName', type: 'string', example: 'Luxury Hotel'),
        new OAT\Property(property: 'totalRevenue', type: 'number', format: 'float', example: 2500.50),
        new OAT\Property(property: 'date', type: 'string', format: 'date', example: '2025-05-01'),
    ]
)]
class RevenueStatResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'hotelId' => $this->hotel_id,
            'hotelName' => $this->hotel_name,
            'totalRevenue' => $this->total_revenue,
            'date' => $this->date,
        ];
    }
}
