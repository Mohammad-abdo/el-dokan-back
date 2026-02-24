<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * منتجات الشركة (أدوية، تراكيب، أخرى) - نظام منفصل عن منتجات المتاجر.
 */
class CompanyProduct extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_DRUG = 'drug';
    const TYPE_COMPOUND = 'compound';
    const TYPE_OTHER = 'other';

    protected $fillable = [
        'shop_id',
        'name',
        'name_ar',
        'sku',
        'description',
        'description_ar',
        'product_type',
        'unit',
        'unit_price',
        'stock_quantity',
        'images',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'images' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function getFirstImageUrlAttribute(): ?string
    {
        $images = $this->images;
        if (empty($images) || !is_array($images)) {
            return null;
        }
        $first = $images[0] ?? null;
        if (is_string($first)) {
            return self::makeFullImageUrl($first);
        }
        return null;
    }

    public static function makeFullImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $base = rtrim(config('app.url'), '/');
        return $base . (str_starts_with($path, '/') ? $path : '/' . $path);
    }
}
