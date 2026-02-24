<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopBranch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminShopBranchController extends Controller
{
    public function index(Shop $shop): JsonResponse
    {
        $branches = $shop->branches()->orderBy('sort_order')->get();
        return response()->json(['success' => true, 'data' => $branches]);
    }

    public function store(Request $request, Shop $shop): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        if ($shop->isCompany()) {
            $plan = $shop->companyPlan;
            if ($plan && $plan->max_branches > 0 && $shop->branches()->count() >= $plan->max_branches) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company plan limit reached for branches (max: ' . $plan->max_branches . '). Upgrade plan to add more.',
                ], 422);
            }
        }
        $data = $validator->validated();
        $data['shop_id'] = $shop->id;
        $branch = ShopBranch::create($data);
        return response()->json(['success' => true, 'data' => $branch], 201);
    }

    public function update(Request $request, Shop $shop, ShopBranch $branch): JsonResponse
    {
        if ($branch->shop_id != $shop->id) {
            return response()->json(['success' => false, 'message' => 'Branch not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $branch->update($validator->validated());
        return response()->json(['success' => true, 'data' => $branch->fresh()]);
    }

    public function destroy(Shop $shop, ShopBranch $branch): JsonResponse
    {
        if ($branch->shop_id != $shop->id) {
            return response()->json(['success' => false, 'message' => 'Branch not found'], 404);
        }
        $branch->delete();
        return response()->json(['success' => true, 'message' => 'Branch deleted']);
    }
}
