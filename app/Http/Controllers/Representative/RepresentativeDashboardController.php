<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\Representative;
use App\Models\Product;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class RepresentativeDashboardController extends Controller
{
    /**
     * Get representative dashboard statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get representative associated with user
        $representative = Representative::where('user_id', $user->id)->first();
        
        if (!$representative) {
            return response()->json([
                'success' => false,
                'message' => 'Representative profile not found for this user'
            ], 404);
        }

        // Get statistics
        $totalProducts = Product::where('is_active', true)->count(); // All active products
        $totalVisits = Visit::where('representative_id', $representative->id)->count();
        
        $completedVisits = Visit::where('representative_id', $representative->id)
            ->whereIn('status', ['completed', 'done'])
            ->count();
        
        $pendingVisits = Visit::where('representative_id', $representative->id)
            ->whereIn('status', ['pending', 'scheduled', 'approved'])
            ->count();
        
        // Today's visits
        $todayVisits = Visit::where('representative_id', $representative->id)
            ->whereDate('visit_date', Carbon::today())
            ->count();
        
        // Calculate total earnings (if there's a commission system)
        $totalEarnings = Visit::where('representative_id', $representative->id)
            ->whereIn('status', ['completed', 'done'])
            ->sum('commission') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'total_visits' => $totalVisits,
                'completed_visits' => $completedVisits,
                'pending_visits' => $pendingVisits,
                'today_visits' => $todayVisits,
                'total_earnings' => $totalEarnings,
            ]
        ]);
    }

    /**
     * Get representative earnings transactions (completed visits)
     * GET /representative/earnings/transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $representative = Representative::where('user_id', $user->id)->first();

        if (!$representative) {
            return response()->json([
                'success' => false,
                'message' => 'Representative profile not found for this user'
            ], 404);
        }

        $visits = Visit::where('representative_id', $representative->id)
            ->whereIn('status', ['completed', 'done'])
            ->with('shop:id,name')
            ->latest('visit_date')
            ->paginate(20);

        $transactions = $visits->getCollection()->map(function ($v) {
            return [
                'id' => $v->id,
                'type' => 'visit',
                'amount' => $v->commission ?? 0,
                'shop_id' => $v->shop_id,
                'shop_name' => $v->shop?->name,
                'visit_date' => $v->visit_date?->toDateString(),
                'status' => $v->status,
                'created_at' => $v->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transactions->values(),
            'meta' => [
                'current_page' => $visits->currentPage(),
                'total' => $visits->total(),
                'per_page' => $visits->perPage(),
            ]
        ]);
    }
}

