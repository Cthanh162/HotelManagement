<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'User_Role';

    protected $primaryKey = null;

    public $timestamps = true;

    protected $fillable = [
        'userId',
        'roleId',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }
}
