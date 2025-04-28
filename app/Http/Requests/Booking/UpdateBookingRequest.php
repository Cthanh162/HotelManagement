<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'UpdateBookingRequest',
    properties: [
        new OAT\Property(property: 'roomId', type: 'integer', example: 1),
        new OAT\Property(property: 'userId', type: 'integer', example: 5),
        new OAT\Property(property: 'checkinTime', type: 'string', format: 'date-time', example: '2025-06-01T12:00:00Z'),
        new OAT\Property(property: 'checkoutTime', type: 'string', format: 'date-time', example: '2025-06-05T12:00:00Z'),
        new OAT\Property(property: 'status', type: 'string', example: 'cancelled'),
        new OAT\Property(property: 'paymentStatus', type: 'string', example: 'paid'),
        new OAT\Property(property: 'createdBy', type: 'integer', example: 1),
        new OAT\Property(property: 'totalPrice', type: 'number', format: 'float', example: 600.00),
    ]
)]
class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'roomId' => 'sometimes|integer|exists:rooms,id',
            'userId' => 'sometimes|integer|exists:users,id',
            'checkinTime' => 'sometimes|date',
            'checkoutTime' => 'sometimes|date|after:checkinTime',
            'status' => 'sometimes|string',
            'paymentStatus' => 'sometimes|string',
            'createdBy' => 'sometimes|integer',
            'totalPrice' => 'sometimes|numeric',
        ];
    }
}