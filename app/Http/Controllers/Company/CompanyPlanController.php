<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * الخطة والحدود — للشركة المسجلة (عرض الخطة والاستخدام).
 */
class CompanyPlanController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->first();
        if (!$shop || !$shop->isCompany()) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $shop->load('companyPlan');
        $plan = $shop->companyPlan;

        $usage = [
            'products' => $shop->companyProducts()->count(),
            'branches' => $shop->branches()->count(),
            'representatives' => $shop->representatives()->count(),
        ];

        $data = [
            'vendor_status' => $shop->vendor_status ?? 'approved',
            'plan' => null,
            'plan_usage' => $usage,
        ];

        if ($plan) {
            $data['plan'] = [
                'id' => $plan->id,
                'name' => $plan->name,
                'name_ar' => $plan->name_ar,
                'slug' => $plan->slug,
                'max_products' => (int) $plan->max_products,
                'max_branches' => (int) $plan->max_branches,
                'max_representatives' => (int) $plan->max_representatives,
                'price' => (float) $plan->price,
                'features' => $plan->features,
                'is_active' => $plan->is_active ?? true,
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }
}
