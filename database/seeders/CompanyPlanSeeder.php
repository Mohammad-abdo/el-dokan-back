<?php

namespace Database\Seeders;

use App\Models\CompanyPlan;
use Illuminate\Database\Seeder;

class CompanyPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'name_ar' => 'أساسي',
                'slug' => 'basic',
                'max_products' => 50,
                'max_branches' => 2,
                'max_representatives' => 5,
                'price' => 0,
                'features' => ['company_products', 'branches', 'representatives', 'visits', 'company_orders', 'wallet', 'documents'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Advanced',
                'name_ar' => 'متقدم',
                'slug' => 'advanced',
                'max_products' => 200,
                'max_branches' => 5,
                'max_representatives' => 20,
                'price' => 99.99,
                'features' => ['company_products', 'branches', 'representatives', 'visits', 'company_orders', 'wallet', 'documents', 'reports', 'api_access'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Unlimited',
                'name_ar' => 'غير محدود',
                'slug' => 'unlimited',
                'max_products' => 0,
                'max_branches' => 0,
                'max_representatives' => 0,
                'price' => 299.99,
                'features' => ['company_products', 'branches', 'representatives', 'visits', 'company_orders', 'wallet', 'documents', 'reports', 'api_access', 'priority_support'],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $p) {
            CompanyPlan::updateOrCreate(
                ['slug' => $p['slug']],
                array_merge($p, ['is_active' => true])
            );
        }
    }
}
