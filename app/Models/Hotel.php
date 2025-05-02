<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotel extends Model
{
    use HasFactory;

    protected $table = 'hotels'; 
    protected $primaryKey = 'hotelId'; 
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'hotelName',
        'address',
        'phoneNumber',
        'nearbyPlaces',
    ];
    public function floors()
    {
        return $this->hasMany(Floor::class, 'hotelId');
    }

}
