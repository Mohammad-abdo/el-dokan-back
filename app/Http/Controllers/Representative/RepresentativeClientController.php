<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\Representative;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RepresentativeClientController extends Controller
{
    /**
     * List clients (shops the representative has visited)
     */
    public function index(Request $request): JsonResponse
    {
        $rep = $request->user()->representative;
        if (!$rep) {
            return response()->json(['success' => false, 'message' => 'Representative not found'], 404);
        }

        $search = $request->input('search');
        $shopIds = $rep->visits()->distinct()->pluck('shop_id')->filter()->unique()->values();

        $query = Shop::whereIn('id', $shopIds);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $shops = $query->get();
        $data = $shops->map(function ($shop) use ($rep) {
            $lastVisit = $rep->visits()->where('shop_id', $shop->id)->latest()->first();
            return [
                'id' => $shop->id,
                'name' => $shop->name,
                'phone' => $shop->phone,
                'address' => $shop->address,
                'last_visit_date' => $lastVisit ? $lastVisit->visit_date?->format('Y-m-d') : null,
                'last_visit_time' => $lastVisit ? $lastVisit->visit_time : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
