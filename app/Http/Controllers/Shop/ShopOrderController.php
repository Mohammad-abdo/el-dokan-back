<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ShopOrderController extends Controller
{
    /**
     * Get shop orders
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->first();
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found'
            ], 404);
        }

        $query = Order::where('shop_id', $shop->id)
            ->with(['user', 'items.product']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->first();
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found'
            ], 404);
        }

        $order = Order::where('shop_id', $shop->id)
            ->where('id', $id)
            ->with(['user', 'items.product', 'delivery'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->first();
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found'
            ], 404);
        }

        $order = Order::where('shop_id', $shop->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,completed,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }
}

