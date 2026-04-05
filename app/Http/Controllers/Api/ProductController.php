<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true)->with(['shop', 'category', 'subcategory']);

        // Filter by shop
        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } elseif ($request->filled('category')) {
            $c = $request->category;
            $query->whereHas('category', function ($q) use ($c) {
                $q->where('name', $c)->orWhere('slug', $c)->orWhere('name_ar', $c)->orWhere('name_en', $c);
            });
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        } elseif ($request->filled('subcategory')) {
            $s = $request->subcategory;
            $query->whereHas('subcategory', function ($q) use ($s) {
                $q->where('name', $s)->orWhere('slug', $s)->orWhere('name_ar', $s)->orWhere('name_en', $s);
            });
        }

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): JsonResponse
    {
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->load('shop');

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Filter products
     */
    public function filter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'nullable|exists:shops,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string',
            'subcategory' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Product::where('is_active', true)->with(['shop', 'category', 'subcategory']);

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } elseif ($request->filled('category')) {
            $c = $request->category;
            $query->whereHas('category', function ($q) use ($c) {
                $q->where('name', $c)->orWhere('slug', $c)->orWhere('name_ar', $c)->orWhere('name_en', $c);
            });
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        } elseif ($request->filled('subcategory')) {
            $s = $request->subcategory;
            $query->whereHas('subcategory', function ($q) use ($s) {
                $q->where('name', $s)->orWhere('slug', $s)->orWhere('name_ar', $s)->orWhere('name_en', $s);
            });
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('rating')) {
            $query->where('rating', '>=', $request->rating);
        }

        $products = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
