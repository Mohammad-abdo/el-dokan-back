<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'shop', 'items.product']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by shop
        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        $orders = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Orders available for delivery (no driver assigned yet) - for admin dashboard
     */
    public function availableForDelivery(Request $request): JsonResponse
    {
        $query = Order::whereIn('status', ['received', 'confirmed'])
            ->where(function ($q) {
                $q->whereDoesntHave('delivery')
                    ->orWhereHas('delivery', fn ($q2) => $q2->whereNull('driver_id'));
            })
            ->with(['user', 'shop', 'deliveryAddress', 'delivery']);

        $orders = $query->latest()->paginate(20);
        $data = $orders->getCollection()->map(function ($order) {
            $shop = $order->shop;
            $addr = $order->deliveryAddress;
            return [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user' => $order->user,
                'shop' => $shop,
                'pickup_address' => $shop ? $shop->address : null,
                'delivery_address' => $addr ? ($addr->detailed_address ?? ($addr->district ?? '') . ', ' . ($addr->city ?? '')) : null,
                'delivery_fee' => (float) $order->delivery_fee,
                'total_amount' => (float) $order->total_amount,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['user', 'shop', 'items.product', 'deliveryAddress', 'statusHistory', 'delivery', 'delivery.driver', 'delivery.driver.user']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:received,processing,on_the_way,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Create status history with timestamp for this stage
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $request->status,
            'description' => $request->notes ?? null,
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order->load('statusHistory')
        ]);
    }

    /**
     * Remove the specified order
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }
}
