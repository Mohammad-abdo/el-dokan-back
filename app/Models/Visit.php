<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'representative_id',
        'shop_id',
        'doctor_id',
        'visit_date',
        'visit_time',
        'purpose',
        'notes',
        'files',
        'status',
        'rejection_reason',
        'doctor_confirmed_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'files' => 'array',
        'doctor_confirmed_at' => 'datetime',
    ];

    public function representative()
    {
        return $this->belongsTo(Representative::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function companyOrders()
    {
        return $this->hasMany(CompanyOrder::class);
    }
}
