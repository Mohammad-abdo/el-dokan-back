<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    public const VENDOR_STATUS_PENDING = 'pending_approval';
    public const VENDOR_STATUS_APPROVED = 'approved';
    public const VENDOR_STATUS_SUSPENDED = 'suspended';
    public const VENDOR_STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'subcategories',
        'address',
        'latitude',
        'longitude',
        'phone',
        'rating',
        'image_url',
        'is_active',
        'vendor_status',
        'company_plan_id',
    ];

    protected $casts = [
        'subcategories' => 'array',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Return full URL for image (relative paths from storage).
     */
    public function getImageUrlAttribute($value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }
        if (str_starts_with((string) $value, 'http://') || str_starts_with((string) $value, 'https://')) {
            return $value;
        }
        $base = rtrim(config('app.url'), '/');
        return $base . (str_starts_with((string) $value, '/') ? $value : '/' . $value);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function financial()
    {
        return $this->hasOne(ShopFinancial::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function branches()
    {
        return $this->hasMany(ShopBranch::class)->orderBy('sort_order');
    }

    public function documents()
    {
        return $this->hasMany(ShopDocument::class);
    }

    public function walletAdjustments()
    {
        return $this->hasMany(ShopWalletAdjustment::class)->latest();
    }

    public function representatives()
    {
        return $this->hasMany(Representative::class);
    }

    /** منتجات الشركة (كatalog) - منفصلة عن منتجات المتجر. */
    public function companyProducts()
    {
        return $this->hasMany(CompanyProduct::class);
    }

    /** مبيعات الشركة (مندوب يبيع لمتجر/طبيب). */
    public function companyOrders()
    {
        return $this->hasMany(CompanyOrder::class);
    }

    /** خطة الشركة (للشركات فقط). */
    public function companyPlan()
    {
        return $this->belongsTo(CompanyPlan::class, 'company_plan_id');
    }

    /** هل هذا المتجر شركة (حسب المستخدم أو الفئة). */
    public function isCompany(): bool
    {
        if ($this->category === 'company') {
            return true;
        }
        return $this->relationLoaded('user')
            ? ($this->user && $this->user->role === 'company')
            : (optional($this->user)->role === 'company');
    }
}
