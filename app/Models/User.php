<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'userId';

    public $incrementing = true;
    protected $keyType = 'int';
    protected $table = 'users';

    protected $fillable = [
        'userName',
        'address',
        'phone',
        'email',
        'password',
        'isActive',
        'createTime',
        'createdBy',
        'lastUpdateTime',
        'lastUpdatedBy',
        'isSuperAdmin',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'createTime' => 'datetime',
        'lastUpdateTime' => 'datetime',
        'isActive' => 'boolean',
        'isSuperAdmin' => 'boolean',
    ];
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'User_Role', 'userId', 'roleId');
    }

}
