<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Order;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ShopDashboardController extends Controller
{
    /**
     * Get shop dashboard statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get shop associated with user
        $shop = Shop::where('user_id', $user->id)->first();
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found for this user'
            ], 404);
        }

        // Get statistics
        $totalProducts = Product::where('shop_id', $shop->id)->count();
        $totalOrders = Order::where('shop_id', $shop->id)->count();
        $pendingOrders = Order::where('shop_id', $shop->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();
        $completedOrders = Order::where('shop_id', $shop->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->count();
        
        // Get unique customers
        $totalCustomers = Order::where('shop_id', $shop->id)
            ->distinct('user_id')
            ->count('user_id');
        
        // Calculate total revenue
        $totalRevenue = Order::where('shop_id', $shop->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('total');

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_customers' => $totalCustomers,
                'total_revenue' => $totalRevenue,
            ]
        ]);
    }

    /**
     * Get shop financial transactions
     * GET /shop/financial/transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found for this user'
            ], 404);
        }

        $transactions = FinancialTransaction::where('shop_id', $shop->id)
            ->with('order:id,order_number,total_amount')
            ->latest()
            ->paginate(20);

        $data = $transactions->getCollection()->map(function ($t) {
            return [
                'id' => $t->id,
                'type' => $t->type,
                'amount' => $t->amount,
                'commission' => $t->commission,
                'status' => $t->status,
                'order_id' => $t->order_id,
                'order_number' => $t->order?->order_number,
                'created_at' => $t->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
            ]
        ]);
    }
}

