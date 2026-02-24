<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\CompanyProduct;
use App\Models\CompanyOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * لوحة تحكم الشركة — إحصائيات منتجات الشركة، مبيعات  مندوبين المبيعات ، المندوبون، الزيارات، الخطة.
 */
class CompanyDashboardController extends Controller
{
    private function getCompanyShop(Request $request): ?Shop
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->first();
        if (!$shop || !$shop->isCompany()) {
            return null;
        }
        return $shop;
    }

    public function dashboard(Request $request): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found for this user'], 404);
        }

        $shop->load('companyPlan');
        $plan = $shop->companyPlan;

        $totalCompanyProducts = $shop->companyProducts()->count();
        $totalCompanyOrders = $shop->companyOrders()->count();
        $totalRepresentatives = $shop->representatives()->count();
        $totalVisits = $shop->representatives()->withCount('visits')->get()->sum('visits_count');
        $totalBranches = $shop->branches()->count();

        $revenue = (float) $shop->companyOrders()->whereIn('status', ['completed', 'delivered', 'confirmed'])->sum('total_amount');
        $pendingOrders = $shop->companyOrders()->whereIn('status', ['pending', 'processing'])->count();

        $planUsage = [
            'products' => $totalCompanyProducts,
            'products_limit' => $plan ? (int) $plan->max_products : 0,
            'branches' => $totalBranches,
            'branches_limit' => $plan ? (int) $plan->max_branches : 0,
            'representatives' => $totalRepresentatives,
            'representatives_limit' => $plan ? (int) $plan->max_representatives : 0,
        ];

        $financial = $shop->financial;
        $walletBalance = $financial ? (float) ($financial->available_balance ?? $financial->total_revenue ?? 0) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'company' => [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'vendor_status' => $shop->vendor_status ?? 'approved',
                ],
                'total_company_products' => $totalCompanyProducts,
                'total_company_orders' => $totalCompanyOrders,
                'pending_company_orders' => $pendingOrders,
                'total_representatives' => $totalRepresentatives,
                'total_visits' => $totalVisits,
                'total_branches' => $totalBranches,
                'revenue' => $revenue,
                'wallet_balance' => $walletBalance,
                'plan' => $plan ? [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'name_ar' => $plan->name_ar,
                    'slug' => $plan->slug,
                    'max_products' => (int) $plan->max_products,
                    'max_branches' => (int) $plan->max_branches,
                    'max_representatives' => (int) $plan->max_representatives,
                    'features' => $plan->features,
                ] : null,
                'plan_usage' => $planUsage,
            ],
        ]);
    }
}
