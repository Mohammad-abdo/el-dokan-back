<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DriverDashboardController extends Controller
{
    /**
     * Get driver dashboard statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get driver associated with user
        $driver = Driver::where('user_id', $user->id)->first();
        
        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found for this user'
            ], 404);
        }

        // Get statistics
        $totalDeliveries = Delivery::where('driver_id', $driver->id)->count();
        
        $pendingDeliveries = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', ['pending', 'assigned'])
            ->count();
        
        $completedDeliveries = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->count();
        
        $activeDeliveries = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', ['in_transit', 'picked_up'])
            ->count();
        
        // Today's deliveries
        $todayDeliveries = Delivery::where('driver_id', $driver->id)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Calculate total earnings (assuming there's a commission or payment field)
        $totalEarnings = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('driver_commission') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_deliveries' => $totalDeliveries,
                'pending_deliveries' => $pendingDeliveries,
                'completed_deliveries' => $completedDeliveries,
                'active_deliveries' => $activeDeliveries,
                'today_deliveries' => $todayDeliveries,
                'total_earnings' => $totalEarnings,
            ]
        ]);
    }

    /**
     * Get driver earnings transactions (deliveries with earnings)
     * GET /driver/earnings/transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found for this user'
            ], 404);
        }

        $deliveries = Delivery::where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->with('order:id,order_number,total_amount,created_at')
            ->latest()
            ->paginate(20);

        $transactions = $deliveries->getCollection()->map(function ($d) {
            return [
                'id' => $d->id,
                'type' => 'delivery',
                'amount' => $d->driver_offer ?? 0,
                'order_id' => $d->order_id,
                'order_number' => $d->order?->order_number,
                'status' => $d->status,
                'created_at' => $d->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transactions->values(),
            'meta' => [
                'current_page' => $deliveries->currentPage(),
                'total' => $deliveries->total(),
                'per_page' => $deliveries->perPage(),
            ]
        ]);
    }
}

