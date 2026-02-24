<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_orders',
        'total_revenue',
        'total_commission',
        'total_withdrawals',
    ];

    protected $casts = [
        'date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'total_withdrawals' => 'decimal:2',
    ];
}
