<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CompanyOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RepresentativeOrderController extends Controller
{
    /**
     * List orders: if rep belongs to company, return company_orders (rep's sales); else market orders from visited shops.
     */
    public function index(Request $request): JsonResponse
    {
        $rep = $request->user()->representative;
        if (!$rep) {
            return response()->json(['success' => false, 'message' => 'Representative not found'], 404);
        }

        if ($rep->shop_id) {
            $query = CompanyOrder::where('representative_id', $rep->id)
                ->with(['visit', 'items.companyProduct', 'customerShop', 'customerDoctor']);
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            $orders = $query->latest()->paginate($request->per_page ?? 15);
            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'type' => 'company_orders',
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ]);
        }

        $shopIds = $rep->visits()->distinct()->pluck('shop_id')->filter()->unique()->values();
        if ($shopIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'type' => 'market_orders',
                'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 0],
            ]);
        }

        $query = Order::whereIn('shop_id', $shopIds)->with(['shop', 'user', 'deliveryAddress']);
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $orders = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'type' => 'market_orders',
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Show single order (company_order or market order by id).
     */
    public function show(Request $request, $id): JsonResponse
    {
        $rep = $request->user()->representative;
        if (!$rep) {
            return response()->json(['success' => false, 'message' => 'Representative not found'], 404);
        }

        if ($rep->shop_id) {
            $order = CompanyOrder::where('representative_id', $rep->id)->with(['visit', 'items.companyProduct', 'customerShop', 'customerDoctor'])->find($id);
            if ($order) {
                return response()->json(['success' => true, 'data' => $order, 'type' => 'company_order']);
            }
        }

        $shopIds = $rep->visits()->distinct()->pluck('shop_id')->filter()->unique()->values();
        $order = Order::with(['shop', 'user', 'deliveryAddress', 'items.product'])
            ->whereIn('shop_id', $shopIds)
            ->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $order, 'type' => 'market_order']);
    }

    /**
     * Cancel order (market order or company order if allowed).
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $rep = $request->user()->representative;
        if (!$rep) {
            return response()->json(['success' => false, 'message' => 'Representative not found'], 404);
        }

        if ($rep->shop_id) {
            $order = CompanyOrder::where('representative_id', $rep->id)->find($id);
            if ($order && in_array($order->status, ['pending', 'processing'])) {
                $order->update(['status' => 'cancelled']);
                return response()->json(['success' => true, 'message' => 'Order cancelled', 'data' => $order->fresh()]);
            }
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }
            return response()->json(['success' => false, 'message' => 'Cannot cancel this order'], 400);
        }

        $shopIds = $rep->visits()->distinct()->pluck('shop_id')->filter()->unique()->values();
        $order = Order::whereIn('shop_id', $shopIds)->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Cannot cancel this order'], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
            'data' => $order->fresh(),
        ]);
    }
}
