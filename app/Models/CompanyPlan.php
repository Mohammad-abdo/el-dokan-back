<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'max_products',
        'max_branches',
        'max_representatives',
        'price',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'max_products' => 'integer',
        'max_branches' => 'integer',
        'max_representatives' => 'integer',
        'price' => 'decimal:2',
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class, 'company_plan_id');
    }

    /**
     * Check if a limit is unlimited (0 = unlimited in our convention).
     */
    public function isUnlimited(string $limitKey): bool
    {
        $v = $this->{$limitKey} ?? 0;
        return $v === 0 || $v === null;
    }
}
