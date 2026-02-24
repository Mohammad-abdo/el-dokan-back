<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\CompanyOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * مبيعات الشركة — طلبات  مندوبين المبيعات  (company_orders) للشركة المسجلة.
 */
class CompanyOrderController extends Controller
{
    private function getCompanyShop(Request $request): ?Shop
    {
        $shop = Shop::where('user_id', $request->user()->id)->first();
        return $shop && $shop->isCompany() ? $shop : null;
    }

    public function index(Request $request): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $query = $shop->companyOrders()->with(['representative.user', 'visit', 'items.companyProduct', 'customerShop', 'customerDoctor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('representative_id')) {
            $query->where('representative_id', $request->representative_id);
        }

        $orders = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $order = $shop->companyOrders()->with(['representative.user', 'visit', 'items.companyProduct', 'customerShop', 'customerDoctor'])->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }
}
