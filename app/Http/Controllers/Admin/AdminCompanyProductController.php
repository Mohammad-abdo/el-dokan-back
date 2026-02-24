<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyPlan;
use App\Models\CompanyProduct;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * منتجات الشركة (أدوية، تراكيب، أخرى) - منفصلة عن منتجات المتاجر.
 */
class AdminCompanyProductController extends Controller
{
    private static function fullImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $base = rtrim(config('app.url'), '/');
        return $base . (str_starts_with($path, '/') ? $path : '/' . $path);
    }

    public function index(Shop $shop): JsonResponse
    {
        $products = $shop->companyProducts()->with('shop')->orderBy('sort_order')->orderBy('name')->get();
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

    public function store(Request $request, Shop $shop): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'product_type' => 'nullable|in:drug,compound,other',
            'unit' => 'nullable|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        if ($shop->isCompany()) {
            $plan = $shop->companyPlan;
            if ($plan && $plan->max_products > 0 && $shop->companyProducts()->count() >= $plan->max_products) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company plan limit reached for products (max: ' . $plan->max_products . '). Upgrade plan to add more.',
                ], 422);
            }
        }
        $data = $validator->validated();
        $data['shop_id'] = $shop->id;
        $data['images'] = $this->resolveImages($request, []);
        $product = CompanyProduct::create($data);
        $product->load('shop');
        $arr = $product->toArray();
        $arr['first_image_url'] = $product->first_image_url;
        if (!empty($arr['images']) && is_array($arr['images'])) {
            $arr['images'] = array_map(fn ($url) => self::fullImageUrl(is_string($url) ? $url : null), $arr['images']);
        }
        return response()->json(['success' => true, 'data' => $arr], 201);
    }

    /**
     * Build images array: existing URLs from input('images') + new uploads (image, images[]).
     */
    private function resolveImages(Request $request, array $existing): array
    {
        $urls = [];
        $imagesInput = $request->input('images');
        if ($imagesInput !== null) {
            $decoded = is_string($imagesInput) ? json_decode($imagesInput, true) : $imagesInput;
            if (is_array($decoded)) {
                foreach ($decoded as $u) {
                    if (is_string($u) && $u !== '') {
                        $urls[] = $u;
                    }
                }
            }
        } else {
            $urls = $existing;
        }
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/company_products', 'public');
            $urls[] = Storage::url($path);
        }
        $imagesInput = $request->file('images');
        if ($imagesInput) {
            $files = is_array($imagesInput) ? $imagesInput : [$imagesInput];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('uploads/company_products', 'public');
                    $urls[] = Storage::url($path);
                }
            }
        }
        return $urls;
    }

    public function show(Shop $shop, CompanyProduct $company_product): JsonResponse
    {
        if ($company_product->shop_id != $shop->id) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        $company_product->load('shop');
        $arr = $company_product->toArray();
        $arr['first_image_url'] = $company_product->first_image_url;
        if (!empty($arr['images']) && is_array($arr['images'])) {
            $arr['images'] = array_map(fn ($url) => self::fullImageUrl(is_string($url) ? $url : null), $arr['images']);
        }
        return response()->json(['success' => true, 'data' => $arr]);
    }

    public function update(Request $request, Shop $shop, CompanyProduct $company_product): JsonResponse
    {
        if ($company_product->shop_id != $shop->id) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'product_type' => 'nullable|in:drug,compound,other',
            'unit' => 'nullable|string|max:50',
            'unit_price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $data['images'] = $this->resolveImages($request, $company_product->images ?? []);
        $company_product->update($data);
        $company_product->refresh();
        $arr = $company_product->toArray();
        $arr['first_image_url'] = $company_product->first_image_url;
        if (!empty($arr['images']) && is_array($arr['images'])) {
            $arr['images'] = array_map(fn ($url) => self::fullImageUrl(is_string($url) ? $url : null), $arr['images']);
        }
        return response()->json(['success' => true, 'data' => $arr]);
    }

    public function destroy(Shop $shop, CompanyProduct $company_product): JsonResponse
    {
        if ($company_product->shop_id != $shop->id) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        $company_product->delete();
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}
