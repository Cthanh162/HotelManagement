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
        new OAT\Property(property: 'phone', type: 'string', example: '0966222776'),
        new OAT\Property(property: 'cccd', type: 'string', example: '001202042004'),
        new OAT\Property(property: 'Name', type: 'string', example: "CHi Thanh"),

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
            'checkinTime' => 'required|date_format:Y-m-d',
            'checkoutTime' => 'required|date_format:Y-m-d|after:checkinTime',
            'totalPrice' => 'nullable|numeric|min:0',
            'Name' => 'required|string|max:255',
            'phone' => 'required|string', // Ví dụ: Số điện thoại Việt Nam
            'cccd' => 'required|string',
        ];
    }
}