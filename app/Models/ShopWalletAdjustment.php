<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopWalletAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'amount',
        'type',
        'description',
        'admin_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
