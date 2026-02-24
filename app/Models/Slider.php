<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'link_type',
        'link_id',
        'link_url',
        'vendor_type', // shop, doctor, driver, representative, general
        'vendor_id',
        'order',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get full URL for image when reading (for API display).
     * Stored value may be relative; this ensures frontend always gets a full URL.
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
}
