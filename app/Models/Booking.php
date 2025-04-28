<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'Booking';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'roomId',
        'userId',
        'checkinTime',
        'checkoutTime',
        'status',
        'paymentStatus',
        'create_at',
        'createdBy',
        'totalPrice',
    ];
    protected $casts = [
        'checkinTime' => 'datetime',
        'checkoutTime' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
