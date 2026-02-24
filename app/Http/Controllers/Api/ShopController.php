<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    /**
     * Display a listing of shops
     */
    public function index(Request $request): JsonResponse
    {
        $query = Shop::where('is_active', true)->with('products');

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $shops = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $shops
        ]);
    }

    /**
     * Display the specified shop
     */
    public function show(Shop $shop): JsonResponse
    {
        if (!$shop->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found'
            ], 404);
        }

        $shop->load(['products' => function ($query) {
            $query->where('is_active', true);
        }]);

        return response()->json([
            'success' => true,
            'data' => $shop
        ]);
    }
}
