<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * إدارة خطط الشركات (Plans) حسب Figma – Plans, Access and Permissions.
 */
class AdminCompanyPlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = CompanyPlan::orderBy('sort_order')->orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $plans]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'slug' => 'required|string|max:64|unique:company_plans,slug',
            'max_products' => 'nullable|integer|min:0',
            'max_branches' => 'nullable|integer|min:0',
            'max_representatives' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $data['max_products'] = $data['max_products'] ?? 50;
        $data['max_branches'] = $data['max_branches'] ?? 3;
        $data['max_representatives'] = $data['max_representatives'] ?? 10;
        $data['price'] = $data['price'] ?? 0;
        $plan = CompanyPlan::create($data);
        return response()->json(['success' => true, 'data' => $plan], 201);
    }

    public function show(CompanyPlan $company_plan): JsonResponse
    {
        $company_plan->loadCount('shops');
        return response()->json(['success' => true, 'data' => $company_plan]);
    }

    public function update(Request $request, CompanyPlan $company_plan): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'slug' => 'sometimes|string|max:64|unique:company_plans,slug,' . $company_plan->id,
            'max_products' => 'nullable|integer|min:0',
            'max_branches' => 'nullable|integer|min:0',
            'max_representatives' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $company_plan->update($validator->validated());
        return response()->json(['success' => true, 'data' => $company_plan->fresh()]);
    }

    public function destroy(CompanyPlan $company_plan): JsonResponse
    {
        if ($company_plan->shops()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete plan: companies are using it. Assign another plan to them first.',
            ], 422);
        }
        $company_plan->delete();
        return response()->json(['success' => true, 'message' => 'Plan deleted']);
    }
}
