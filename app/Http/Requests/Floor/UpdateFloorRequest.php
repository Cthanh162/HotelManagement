<?php

namespace App\Http\Requests\Floor;


use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'UpdateFloorRequest',
    properties: [
        new OAT\Property(property: 'floorName', type: 'string', example: 'Lầu 5'),
    ]
)]
class UpdateFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'floorName' => 'sometimes|string|max:255',
        ];
    }
}
