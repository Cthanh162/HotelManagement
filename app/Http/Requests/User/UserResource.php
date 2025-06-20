<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'User',
    properties: [
        new OAT\Property(property: 'userId', type: 'integer', example: 1),
        new OAT\Property(property: 'userName', type: 'string', example: 'John Doe'),
        new OAT\Property(property: 'email', type: 'string', example: 'john@example.com'),
        new OAT\Property(property: 'role', type: 'string', example: 'admin'),
        new OAT\Property(property: 'isActive', type: 'boolean', example: true),
    ]
)]
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'userId' => $this->userId,
            'userName' => $this->userName,
            'email' => $this->email,
            'role' => $this->role,
            'isActive' => $this->isActive,
        ];
    }
}