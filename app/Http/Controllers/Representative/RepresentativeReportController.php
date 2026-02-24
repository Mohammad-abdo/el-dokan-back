<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class RepresentativeReportController extends Controller
{
    /**
     * Generate report by type and date range
     * type: visits | orders | sales
     */
    public function index(Request $request): JsonResponse
    {
        $rep = $request->user()->representative;
        if (!$rep) {
            return response()->json(['success' => false, 'message' => 'Representative not found'], 404);
        }

        $request->validate([
            'type' => 'required|in:visits,orders,sales',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $from = Carbon::parse($request->from_date)->startOfDay();
        $to = Carbon::parse($request->to_date)->endOfDay();

        $shopIds = $rep->visits()->distinct()->pluck('shop_id')->filter()->unique()->values();

        $data = [];
        switch ($request->type) {
            case 'visits':
                $items = Visit::where('representative_id', $rep->id)
                    ->whereBetween('visit_date', [$from, $to])
                    ->with('shop')
                    ->orderBy('visit_date')
                    ->get();
                $data = [
                    'summary' => [
                        'total_visits' => $items->count(),
                        'from_date' => $from->toDateString(),
                        'to_date' => $to->toDateString(),
                    ],
                    'items' => $items->map(fn ($v) => [
                        'id' => $v->id,
                        'visit_date' => $v->visit_date?->format('Y-m-d'),
                        'visit_time' => $v->visit_time,
                        'shop_name' => $v->shop?->name,
                        'purpose' => $v->purpose,
                        'status' => $v->status,
                    ]),
                ];
                break;
            case 'orders':
                $query = Order::whereIn('shop_id', $shopIds)->whereBetween('created_at', [$from, $to]);
                $orders = $query->with('shop', 'user')->orderBy('created_at', 'desc')->get();
                $data = [
                    'summary' => [
                        'total_orders' => $orders->count(),
                        'total_amount' => round($orders->sum('total_amount'), 2),
                        'from_date' => $from->toDateString(),
                        'to_date' => $to->toDateString(),
                    ],
                    'items' => $orders->map(fn ($o) => [
                        'id' => $o->id,
                        'order_number' => $o->order_number,
                        'date' => $o->created_at->format('Y-m-d H:i'),
                        'shop_name' => $o->shop?->name,
                        'customer_name' => $o->user?->name ?? $o->user?->username,
                        'status' => $o->status,
                        'total_amount' => (float) $o->total_amount,
                    ]),
                ];
                break;
            case 'sales':
                $salesQuery = Order::whereIn('shop_id', $shopIds)
                    ->whereBetween('created_at', [$from, $to])
                    ->whereIn('status', ['delivered', 'received', 'confirmed']);
                $totalSales = $salesQuery->sum('total_amount');
                $count = $salesQuery->count();
                $salesItems = Order::whereIn('shop_id', $shopIds)
                    ->whereBetween('created_at', [$from, $to])
                    ->whereIn('status', ['delivered', 'received', 'confirmed'])
                    ->with('shop')->orderBy('created_at', 'desc')->get();
                $data = [
                    'summary' => [
                        'total_sales_amount' => round($totalSales, 2),
                        'total_orders_count' => $count,
                        'from_date' => $from->toDateString(),
                        'to_date' => $to->toDateString(),
                    ],
                    'items' => $salesItems->map(fn ($o) => [
                        'order_id' => $o->id,
                        'order_number' => $o->order_number,
                        'date' => $o->created_at->format('Y-m-d'),
                        'shop_name' => $o->shop?->name,
                        'amount' => (float) $o->total_amount,
                    ]),
                ];
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
