<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'CreateReviewRequest',
    required: ['roomId', 'rating', 'des'],
    properties: [
        new OAT\Property(property: 'roomId', type: 'integer', example: 5),
        new OAT\Property(property: 'rating', type: 'number', format: 'float', example: 4.5),
        new OAT\Property(property: 'des', type: 'string', example: 'Nice and clean room.')
    ]
)]
class CreateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'roomId' => 'required|integer|exists:rooms,roomId',
            'rating' => 'required|numeric|min:0|max:5',
            'des' => 'required|string|max:1000'
        ];
    }
}