<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\CompanyProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    /**
     * Display a listing of products.
     * type=market (default) => منتجات المتاجر (Product).
     * type=company => منتجات الشركات (CompanyProduct).
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->get('type', 'market');

        if ($type === 'company') {
            $query = CompanyProduct::with('shop');

            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            }
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('name_ar', 'like', '%' . $request->search . '%');
                });
            }

            $paginator = $query->latest()->paginate(20);
            $paginator->getCollection()->transform(function (CompanyProduct $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name_ar ?: $p->name,
                    'name_ar' => $p->name_ar,
                    'price' => $p->unit_price,
                    'unit_price' => $p->unit_price,
                    'stock' => $p->stock_quantity,
                    'stock_quantity' => $p->stock_quantity,
                    'category' => ['name' => $p->product_type ?? 'other'],
                    'shop' => $p->shop ? ['id' => $p->shop->id, 'name' => $p->shop->name] : null,
                    'shop_id' => $p->shop_id,
                    'status' => $p->is_active ? 'active' : 'inactive',
                    'created_at' => $p->created_at?->toIso8601String(),
                    'first_image_url' => $p->first_image_url,
                    'images' => $p->images ?? [],
                    'product_source' => 'company',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $paginator,
            ]);
        }

        $query = Product::with(['shop', 'category']);

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products,
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
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|text',
            'description_en' => 'nullable|text',
            'short_description' => 'nullable|string',
            'short_description_ar' => 'nullable|string',
            'short_description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate slug if not provided
        $slug = $request->slug;
        if (!$slug) {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $productData = $request->all();
        $productData['slug'] = $slug;

        $product = Product::create($productData);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['shop', 'category', 'subcategory'])
        ], 201);
    }

    /**
     * Display the specified product with complete details
     */
    public function show(Product $product): JsonResponse
    {
        // Load basic relationships
        $product->load(['shop', 'category', 'subcategory']);
        
        // Calculate purchase statistics
        $totalOrders = $product->orderItems()->count();
        $totalQuantitySold = $product->orderItems()->sum('quantity');
        $totalRevenue = $product->orderItems()->sum('total_price');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        // Get recent orders with this product
        $recentOrders = $product->orderItems()
            ->with(['order.user', 'order'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($orderItem) {
                return [
                    'order_id' => $orderItem->order->id,
                    'order_number' => $orderItem->order->order_number,
                    'customer' => $orderItem->order->user->username,
                    'quantity' => $orderItem->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $orderItem->total_price,
                    'order_date' => $orderItem->order->created_at,
                    'status' => $orderItem->order->status,
                ];
            });
        
        // Get ratings and reviews
        $ratings = $product->ratings()
            ->with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'user' => $rating->user->username,
                    'rating' => $rating->rating,
                    'comment' => $rating->comment,
                    'is_approved' => $rating->is_approved,
                    'created_at' => $rating->created_at,
                ];
            });
        
        // Rating statistics
        $ratingStats = [
            'average_rating' => $product->ratings()->avg('rating') ?? 0,
            'total_ratings' => $product->ratings()->count(),
            'approved_ratings' => $product->ratings()->where('is_approved', true)->count(),
            'rating_distribution' => [
                '5_star' => $product->ratings()->where('rating', 5)->count(),
                '4_star' => $product->ratings()->where('rating', 4)->count(),
                '3_star' => $product->ratings()->where('rating', 3)->count(),
                '2_star' => $product->ratings()->where('rating', 2)->count(),
                '1_star' => $product->ratings()->where('rating', 1)->count(),
            ]
        ];
        
        // Get cart statistics
        $cartStats = [
            'current_in_carts' => $product->carts()->count(),
            'total_cart_quantity' => $product->carts()->sum('quantity'),
        ];
        
        // Get favorites count
        $favoritesCount = $product->favourites()->count();
        
        // Prepare product images (full URLs for display)
        $images = $product->images ?? [];
        $imageData = [];
        foreach ($images as $index => $image) {
            $imageData[] = [
                'id' => $index + 1,
                'url' => is_string($image) ? Product::makeFullImageUrl($image) : $image,
                'is_primary' => $index === 0,
            ];
        }
        
        // Available actions for this product
        $availableActions = [
            'edit' => true,
            'delete' => $totalOrders === 0, // Only allow delete if no orders
            'toggle_status' => true,
            'manage_inventory' => true,
            'view_analytics' => true,
            'export_data' => true,
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                // Basic product info
                'product' => $product,
                
                // Images
                'images' => $imageData,
                
                // Purchase statistics
                'purchase_stats' => [
                    'total_orders' => $totalOrders,
                    'total_quantity_sold' => $totalQuantitySold,
                    'total_revenue' => $totalRevenue,
                    'average_order_value' => $averageOrderValue,
                ],
                
                // Recent orders
                'recent_orders' => $recentOrders,
                
                // Ratings and reviews
                'ratings' => $ratings,
                'rating_stats' => $ratingStats,
                
                // Cart statistics
                'cart_stats' => $cartStats,
                
                // Favorites
                'favorites_count' => $favoritesCount,
                
                // Available actions
                'available_actions' => $availableActions,
                
                // Additional metadata
                'metadata' => [
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                    'last_ordered_at' => $product->orderItems()->latest()->first()?->created_at,
                ]
            ]
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|text',
            'description_en' => 'nullable|text',
            'short_description' => 'nullable|string',
            'short_description_ar' => 'nullable|string',
            'short_description_en' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
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
            'data' => $product->load(['shop', 'category', 'subcategory'])
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

    /**
     * Toggle product active status
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        $product->update([
            'is_active' => !$product->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => "Product " . ($product->is_active ? 'activated' : 'deactivated') . " successfully",
            'data' => $product
        ]);
    }

    /**
     * Get product analytics data
     */
    public function analytics(Product $product): JsonResponse
    {
        // Sales over time (last 30 days)
        $salesOverTime = $product->orderItems()
            ->with('order')
            ->whereHas('order', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })
            ->get()
            ->groupBy(function ($item) {
                return $item->order->created_at->format('Y-m-d');
            })
            ->map(function ($items) {
                return [
                    'quantity' => $items->sum('quantity'),
                    'revenue' => $items->sum('total_price'),
                    'orders' => $items->count(),
                ];
            })
            ->toArray();

        // Top customers for this product
        $topCustomers = $product->orderItems()
            ->with('order.user')
            ->get()
            ->groupBy('order.user.id')
            ->map(function ($items) {
                $user = $items->first()->order->user;
                return [
                    'customer' => $user->username,
                    'total_quantity' => $items->sum('quantity'),
                    'total_spent' => $items->sum('total_price'),
                    'order_count' => $items->count(),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(5)
            ->values();

        // Monthly comparison
        $thisMonth = $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            })
            ->get();

        $lastMonth = $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereMonth('created_at', now()->subMonth()->month)
                      ->whereYear('created_at', now()->subMonth()->year);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sales_over_time' => $salesOverTime,
                'top_customers' => $topCustomers,
                'monthly_comparison' => [
                    'this_month' => [
                        'quantity' => $thisMonth->sum('quantity'),
                        'revenue' => $thisMonth->sum('total_price'),
                        'orders' => $thisMonth->count(),
                    ],
                    'last_month' => [
                        'quantity' => $lastMonth->sum('quantity'),
                        'revenue' => $lastMonth->sum('total_price'),
                        'orders' => $lastMonth->count(),
                    ],
                    'growth' => [
                        'quantity_growth' => $lastMonth->sum('quantity') > 0 
                            ? (($thisMonth->sum('quantity') - $lastMonth->sum('quantity')) / $lastMonth->sum('quantity')) * 100 
                            : 0,
                        'revenue_growth' => $lastMonth->sum('total_price') > 0 
                            ? (($thisMonth->sum('total_price') - $lastMonth->sum('total_price')) / $lastMonth->sum('total_price')) * 100 
                            : 0,
                    ]
                ]
            ]
        ]);
    }

    /**
     * Export product data
     */
    public function export(Product $product): JsonResponse
    {
        $productData = $product->load(['shop', 'category', 'subcategory']);
        
        // Get all order history
        $orderHistory = $product->orderItems()
            ->with(['order.user', 'order'])
            ->get()
            ->map(function ($item) {
                return [
                    'order_id' => $item->order->id,
                    'order_number' => $item->order->order_number,
                    'customer' => $item->order->user->username,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'order_date' => $item->order->created_at->format('Y-m-d H:i:s'),
                    'status' => $item->order->status,
                ];
            });

        // Get all ratings
        $ratings = $product->ratings()
            ->with('user')
            ->get()
            ->map(function ($rating) {
                return [
                    'user' => $rating->user->username,
                    'rating' => $rating->rating,
                    'comment' => $rating->comment,
                    'is_approved' => $rating->is_approved,
                    'created_at' => $rating->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'product_info' => $productData,
                'order_history' => $orderHistory,
                'ratings' => $ratings,
                'export_date' => now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
