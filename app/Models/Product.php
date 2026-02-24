<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'name_ar',
        'name_en',
        'description',
        'description_ar',
        'description_en',
        'short_description',
        'short_description_ar',
        'short_description_en',
        'price',
        'discount_percentage',
        'images',
        'rating',
        'stock_quantity',
        'category',
        'subcategory',
        'category_id',
        'subcategory_id',
        'is_active',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['first_image_url'];

    /**
     * Convert a relative image path to full URL for API display.
     */
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

    /**
     * First product image as full URL (for list display).
     */
    public function getFirstImageUrlAttribute(): ?string
    {
        $images = $this->images;
        if (!is_array($images) || empty($images)) {
            return null;
        }
        $first = $images[0];
        return is_string($first) ? self::makeFullImageUrl($first) : null;
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}