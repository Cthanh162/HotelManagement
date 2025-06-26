<?php 
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'StoreUserRequest',
    required: ['userName', 'email', 'password'],
    properties: [
        new OAT\Property(property: 'userName', type: 'string', example: 'John Doe'),
        new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
        new OAT\Property(property: 'role', type: 'string', example: 'admin'),
        new OAT\Property(property: 'isActive', type: 'boolean', example: true),
    ]
)]
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullName' => 'sometimes|required|string|max:255',
            'userName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',   
            'role' => 'nullable|string|max:50',
            'isActive' => 'nullable|boolean',
        ];
    }
}