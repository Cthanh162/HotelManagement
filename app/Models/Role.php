<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $table = 'Role';

    protected $primaryKey = 'roleId';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; 

    protected $fillable = [
        'roleName',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'User_Role', 'roleId', 'userId');
    }

}
