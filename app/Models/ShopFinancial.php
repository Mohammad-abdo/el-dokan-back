<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopFinancial extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'total_revenue',
        'total_commission',
        'pending_balance',
        'available_balance',
        'commission_rate',
        'profit_share_percentage',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'profit_share_percentage' => 'decimal:2',
    ];

    /**
     * نسبة الدكان من الأرباح (إن لم تُحدد تُحسب من commission_rate: 100 - commission_rate)
     */
    public function getShopProfitSharePercentageAttribute(): float
    {
        if ($this->profit_share_percentage !== null && $this->profit_share_percentage !== '') {
            return (float) $this->profit_share_percentage;
        }
        $rate = (float) ($this->commission_rate ?? 0);
        return max(0, 100 - $rate);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
