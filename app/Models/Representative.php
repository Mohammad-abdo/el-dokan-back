<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Representative extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'employee_id',
        'territory',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function companyOrders()
    {
        return $this->hasMany(CompanyOrder::class);
    }
}
