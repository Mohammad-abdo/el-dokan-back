<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\CompanyProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RepresentativeProductController extends Controller
{
    /**
     * Display a listing of products.
     * If representative belongs to a company (shop_id), return company_products; else market products.
     */
    public function index(Request $request): JsonResponse
    {
        $representative = $request->user()->representative;

        if (!$representative) {
            return response()->json([
                'success' => false,
                'message' => 'Representative profile not found'
            ], 404);
        }

        if ($representative->shop_id) {
            $shop = $representative->shop;
            if (!$shop || !$shop->isCompany()) {
                return response()->json(['success' => false, 'message' => 'Company not found'], 404);
            }
            $products = $shop->companyProducts()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
            $products = $products->map(function ($p) {
                $arr = $p->toArray();
                $arr['first_image_url'] = $p->first_image_url;
                return $arr;
            });
            return response()->json(['success' => true, 'data' => $products, 'type' => 'company_products']);
        }

        $products = Product::where('is_active', true)->with('shop')->latest()->paginate(20);
        return response()->json([
            'success' => true,
            'data' => $products,
            'type' => 'market_products'
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load('shop')
        ], 201);
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('shop');

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'images' => 'nullable|array',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load('shop')
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
