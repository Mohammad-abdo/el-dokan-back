<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_order_id',
        'company_product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function companyOrder()
    {
        return $this->belongsTo(CompanyOrder::class);
    }

    public function companyProduct()
    {
        return $this->belongsTo(CompanyProduct::class);
    }
}
