<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $primaryKey = 'roomId';

    public $timestamps = false;
    protected $casts = [
        'roomImages' => 'array',
        // Thêm các casts khác nếu cần
    ];
    protected $fillable = [
        'userId',
        'hotelId',
        'floorId',
        'roomName',
        'status',
        'roomType',
        'capacity',
        'price',
        'description',
        'roomVideo',
        'roomImages',
        'createdAt',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotelId');
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floorId');
    }
    public function services()
    {
        return $this->belongsToMany(Service::class, 'RoomService', 'roomId', 'serviceId');
    }

}
