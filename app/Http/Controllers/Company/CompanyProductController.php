<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\CompanyProduct;
use App\Models\CompanyPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * منتجات الشركة — للشركة المسجلة دخولها (منتجات كتالوج  مندوبين المبيعات ).
 */
class CompanyProductController extends Controller
{
    private function getCompanyShop(Request $request): ?Shop
    {
        $shop = Shop::where('user_id', $request->user()->id)->first();
        return $shop && $shop->isCompany() ? $shop : null;
    }

    private static function fullImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') return $path;
        if (str_starts_with($path, 'http')) return $path;
        $base = rtrim(config('app.url'), '/');
        return $base . (str_starts_with($path, '/') ? $path : '/' . $path);
    }

    public function index(Request $request): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $products = $shop->companyProducts()->orderBy('sort_order')->orderBy('name')->get();
        $products = $products->map(function (CompanyProduct $p) {
            $arr = $p->toArray();
            $arr['first_image_url'] = $p->first_image_url;
            if (!empty($arr['images']) && is_array($arr['images'])) {
                $arr['images'] = array_map(fn ($url) => self::fullImageUrl(is_string($url) ? $url : null), $arr['images']);
            }
            return $arr;
        });

        return response()->json(['success' => true, 'data' => $products]);
    }

    public function store(Request $request): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $plan = $shop->companyPlan;
        if ($plan && $plan->max_products > 0 && $shop->companyProducts()->count() >= $plan->max_products) {
            return response()->json([
                'success' => false,
                'message' => 'Company plan limit reached for products. Upgrade plan to add more.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'product_type' => 'nullable|in:drug,compound,other',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['shop_id'] = $shop->id;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/company_products', 'public');
            $data['images'] = [Storage::url($path)];
        }
        $product = CompanyProduct::create($data);
        $product->load('shop');
        $arr = $product->toArray();
        $arr['first_image_url'] = $product->first_image_url;
        return response()->json(['success' => true, 'data' => $arr], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $product = $shop->companyProducts()->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $arr = $product->toArray();
        $arr['first_image_url'] = $product->first_image_url;
        return response()->json(['success' => true, 'data' => $arr]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $product = $shop->companyProducts()->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'product_type' => 'nullable|in:drug,compound,other',
            'unit_price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product->update($validator->validated());
        $product->load('shop');
        $arr = $product->toArray();
        $arr['first_image_url'] = $product->first_image_url;
        return response()->json(['success' => true, 'data' => $arr]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $shop = $this->getCompanyShop($request);
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $product = $shop->companyProducts()->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted']);
    }
}
