<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'CreateBookingRequest',
    required: ['roomId', 'userId', 'checkinTime', 'checkoutTime', 'totalPrice'],
    properties: [
        new OAT\Property(property: 'roomId', type: 'integer', example: 1),
        new OAT\Property(property: 'userId', type: 'integer', example: 1),
        new OAT\Property(property: 'checkinTime', type: 'string', format: 'date-time', example: '2025-05-01T14:00:00'),
        new OAT\Property(property: 'checkoutTime', type: 'string', format: 'date-time', example: '2025-05-03T12:00:00'),
        new OAT\Property(property: 'totalPrice', type: 'number', format: 'float', example: 250.50),
    ]
)]
class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'roomId' => 'required|integer|exists:rooms,roomId',
            'userId' => 'required|integer|exists:users,userId',
            'checkinTime' => 'required|date|after:now',
            'checkoutTime' => 'required|date|after:checkinTime',
            'totalPrice' => 'required|numeric|min:0',
        ];
    }
}