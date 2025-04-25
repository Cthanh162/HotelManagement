<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomService extends Model
{
    protected $table = 'RoomService';

    public $timestamps = false;

    protected $fillable = [
        'roomId',
        'serviceId',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomId');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'serviceId');
    }
}
