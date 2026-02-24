<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'photo_url',
        'rating',
        'current_location_lat',
        'current_location_lng',
        'status',
    ];

    protected $casts = [
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'is_available' => 'boolean',
    ];

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
