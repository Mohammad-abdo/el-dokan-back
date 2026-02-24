<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DriverDeliveryController extends Controller
{
    /**
     * Get driver deliveries
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = Driver::where('user_id', $user->id)->first();
        
        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        $query = Delivery::where('driver_id', $driver->id)
            ->with(['order', 'order.user']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('filter') && $request->filter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        }

        $deliveries = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $deliveries
        ]);
    }
}

