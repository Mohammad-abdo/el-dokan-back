<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::with('parent', 'children')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'required|string|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'image_url' => 'nullable|string|url',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Custom validation: at least one name field must be provided
        if (empty($request->name) && empty($request->name_ar) && empty($request->name_en)) {
            $validator->errors()->add('name', 'At least one name field (name, name_ar, or name_en) is required.');
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Set name from name_en or name_ar if name is not provided
        $data = $request->all();
        if (empty($data['name'])) {
            $data['name'] = $data['name_en'] ?? $data['name_ar'] ?? '';
        }
        
        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Get subcategories of a category (for admin product form)
     */
    public function subcategories(Category $category): JsonResponse
    {
        $subcategories = Category::where('parent_id', $category->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }

    /**
     * Display the specified category
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children', 'products']);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'sometimes|string|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'image_url' => 'nullable|string|url',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with products'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
