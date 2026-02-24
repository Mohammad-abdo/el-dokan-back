<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopDocument extends Model
{
    use HasFactory;

    const TYPE_PERMIT = 'permit';
    const TYPE_LICENSE = 'license';
    const TYPE_TAX_CARD = 'tax_card';
    const TYPE_COMMERCIAL_REGISTER = 'commercial_register';
    const TYPE_OTHER = 'other';

    protected $fillable = [
        'shop_id',
        'type',
        'title',
        'title_ar',
        'file_url',
        'reference_number',
        'issue_date',
        'expires_at',
        'is_verified',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expires_at' => 'date',
        'is_verified' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
