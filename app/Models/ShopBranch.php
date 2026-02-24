<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'name_ar',
        'address',
        'phone',
        'latitude',
        'longitude',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
