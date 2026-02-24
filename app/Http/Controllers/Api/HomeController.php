<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Get home page data
     */
    public function index(): JsonResponse
    {
        $sliders = Slider::where('is_active', true)->latest()->take(5)->get();
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with('children')
            ->latest()
            ->take(8)
            ->get();
        $featuredProducts = Product::where('is_active', true)
            ->orderBy('rating', 'desc')
            ->take(10)
            ->get();
        $popularShops = Shop::where('is_active', true)
            ->orderBy('rating', 'desc')
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sliders' => $sliders,
                'categories' => $categories,
                'featured_products' => $featuredProducts,
                'popular_shops' => $popularShops,
            ]
        ]);
    }
}
