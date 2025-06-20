<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'Booking',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'roomId', type: 'integer', example: 1),
        new OAT\Property(property: 'userId', type: 'integer', example: 1),
        new OAT\Property(property: 'checkinTime', type: 'string', format: 'date-time', example: '2025-05-01T14:00:00'),
        new OAT\Property(property: 'checkoutTime', type: 'string', format: 'date-time', example: '2025-05-03T12:00:00'),
        new OAT\Property(property: 'status', type: 'string', example: 'PENDING'),
        new OAT\Property(property: 'paymentStatus', type: 'string', example: 'UNPAID'),
        new OAT\Property(property: 'totalPrice', type: 'number', format: 'float', example: 250.50),
        new OAT\Property(property: 'paymentProof', type: 'string'),
        new OAT\Property(property: 'Name', type: 'string'),
        new OAT\Property(property: 'phone', type: 'string'),
        new OAT\Property(property: 'cccd', type: 'string'),

    ]
)]
class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'roomId' => $this->roomId,
            'userId' => $this->userId,
            'checkinTime' => $this->checkinTime ? $this->checkinTime->format('Y-m-d H:i:s') : null,
            'checkoutTime' => $this->checkoutTime ? $this->checkoutTime->format('Y-m-d H:i:s') : null,
            'status' => $this->status,
            'paymentStatus' => $this->paymentStatus,
            'totalPrice' => $this->totalPrice,
            'paymentProof' => $this->paymentProof,
            'Name' => $this->Name,
            'phone' => $this->phone,
            'cccd' => $this->cccd,
            'room' => new RoomResource($this->whenLoaded('room')),
        ];
    }
}