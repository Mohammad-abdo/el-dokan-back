<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Display the specified category
     */
    public function show(Category $category): JsonResponse
    {
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->load(['children', 'products']);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Get subcategories
     */
    public function subcategories(Request $request): JsonResponse
    {
        $parentId = $request->get('parent_id');
        
        $subcategories = Category::where('parent_id', $parentId)
            ->where('is_active', true)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }
}
