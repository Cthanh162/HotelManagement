<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Floor extends Model
{
    use HasFactory;

    protected $table = 'floors'; 
    public $incrementing = true;
    protected $keyType = 'int';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'hotelId',
        'floorName',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotelId');
    }
}
