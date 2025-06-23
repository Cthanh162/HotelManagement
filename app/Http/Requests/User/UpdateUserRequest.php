<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'UpdateUserRequest',
    properties: [
        new OAT\Property(property: 'userName', type: 'string', example: 'John Updated'),
        new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'john_updated@example.com'),
        new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword123'),
        new OAT\Property(property: 'role', type: 'string', example: 'editor'),
        new OAT\Property(property: 'isActive', type: 'boolean', example: false),
    ]
)]
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullName' => 'sometimes|required|string|max:255',
            'userName' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $this->id . ',userId',
            'password' => 'sometimes|required|string|min:6',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'role' => 'nullable|string|max:50',
            'isActive' => 'nullable|boolean',
        ];
    }
}