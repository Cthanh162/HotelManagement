<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'Reviews';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'userId',
        'roomId',
        'rating',
        'des',
        'createdAt',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomId');
    }
}
