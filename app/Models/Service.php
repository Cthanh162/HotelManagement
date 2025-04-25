<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $table = 'Service'; 

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; 

    protected $fillable = [
        'name',
        'status',
        'price',
    ];
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'RoomService', 'serviceId', 'roomId');
    }

}
