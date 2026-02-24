<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{
    /**
     * Display a listing of user's favourite products
     */
    public function index(Request $request): JsonResponse
    {
        $favourites = Favourite::where('user_id', $request->user()->id)
            ->with('product.shop')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $favourites
        ]);
    }

    /**
     * Add product to favourites
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if already favourited
        $existing = Favourite::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in favourites'
            ], 400);
        }

        $favourite = Favourite::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to favourites',
            'data' => $favourite->load('product')
        ], 201);
    }

    /**
     * Remove product from favourites
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        $favourite = Favourite::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if (!$favourite) {
            return response()->json([
                'success' => false,
                'message' => 'Product not in favourites'
            ], 404);
        }

        $favourite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from favourites'
        ]);
    }

    /**
     * Check if product is in favourites
     */
    public function check(Request $request, Product $product): JsonResponse
    {
        $isFavourite = Favourite::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'is_favourite' => $isFavourite
            ]
        ]);
    }
}




