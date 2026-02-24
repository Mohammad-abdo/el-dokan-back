<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * طلب/بيع شركة: مندوب يبيع منتجات الشركة لمتجر أو طبيب (مرتبط بالزيارة).
 */
class CompanyOrder extends Model
{
    use HasFactory;

    const CUSTOMER_TYPE_SHOP = 'shop';
    const CUSTOMER_TYPE_DOCTOR = 'doctor';

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_number',
        'shop_id',
        'representative_id',
        'visit_id',
        'customer_type',
        'customer_id',
        'total_amount',
        'status',
        'notes',
        'ordered_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'ordered_at' => 'datetime',
    ];

    protected $appends = ['customer'];

    protected static function booted()
    {
        static::creating(function (CompanyOrder $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'CO-' . strtoupper(uniqid());
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function representative()
    {
        return $this->belongsTo(Representative::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function items()
    {
        return $this->hasMany(CompanyOrderItem::class);
    }

    /** العميل: متجر أو طبيب */
    public function customerShop()
    {
        return $this->belongsTo(Shop::class, 'customer_id');
    }

    public function customerDoctor()
    {
        return $this->belongsTo(Doctor::class, 'customer_id');
    }

    public function getCustomerAttribute()
    {
        if ($this->relationLoaded('customerShop') && $this->customer_type === self::CUSTOMER_TYPE_SHOP) {
            return $this->customerShop;
        }
        if ($this->relationLoaded('customerDoctor') && $this->customer_type === self::CUSTOMER_TYPE_DOCTOR) {
            return $this->customerDoctor;
        }
        return null;
    }
}
