<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\CompanyOrder;
use App\Models\CompanyPlan;
use App\Models\CompanyProduct;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Representative;
use App\Models\ShopBranch;
use App\Models\ShopDocument;
use App\Models\ShopWalletAdjustment;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminShopController extends Controller
{
    /**
     * Resolve full URL for shop/product images (relative path or already full URL).
     */
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

    /**
     * Handle shop image: from uploaded file (store to disk) or from image_url string.
     * Returns the URL to save on the shop (full URL for display).
     */
    private function resolveShopImageUrl(Request $request): ?string
    {
        $file = $request->file('image') ?? $request->file('image_url');
        if ($file && $file->isValid()) {
            $path = $file->store('uploads/shops', 'public');
            $url = Storage::url($path);
            return self::fullImageUrl($url);
        }
        $url = $request->input('image_url');
        if (is_string($url) && $url !== '') {
            return self::fullImageUrl($url);
        }
        return null;
    }

    /**
     * Display a listing of shops
     */
    public function index(Request $request): JsonResponse
    {
        $query = Shop::with(['user', 'products'])->withCount('products');

        if ($request->has('type') && $request->type === 'company') {
            $query->where(function ($q) {
                $q->where('category', 'company')
                    ->orWhereHas('user', fn ($u) => $u->where('role', 'company'));
            })->with('companyPlan');
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $shops = $query->latest()->paginate(20);

        if ($request->has('type') && $request->type === 'company' && $shops->isNotEmpty()) {
            $shops->getCollection()->transform(function (Shop $shop) {
                $arr = $shop->toArray();
                $arr['is_company'] = true;
                $arr['vendor_status'] = $shop->vendor_status ?? 'approved';
                $arr['company_plan'] = $shop->companyPlan ? [
                    'id' => $shop->companyPlan->id,
                    'name' => $shop->companyPlan->name,
                    'name_ar' => $shop->companyPlan->name_ar,
                    'slug' => $shop->companyPlan->slug,
                ] : null;
                return $arr;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $shops
        ]);
    }

    /**
     * Store a newly created shop (accepts image file or image_url from previous upload).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'subcategories' => 'nullable|array',
            'address' => 'required|string',
            'phone' => 'nullable|string',
            'image_url' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['user_id', 'name', 'category', 'subcategories', 'address', 'phone', 'latitude', 'longitude', 'is_active', 'company_plan_id', 'vendor_status']);
        $data['vendor_status'] = $data['vendor_status'] ?? Shop::VENDOR_STATUS_APPROVED;
        if (($data['category'] ?? '') === 'company' && empty($data['company_plan_id'])) {
            $defaultPlan = CompanyPlan::where('slug', 'basic')->first();
            if ($defaultPlan) {
                $data['company_plan_id'] = $defaultPlan->id;
            }
        }
        $imageUrl = $this->resolveShopImageUrl($request);
        if ($imageUrl !== null) {
            $data['image_url'] = $imageUrl;
        }

        $shop = Shop::create($data);

        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            if ($user) {
                $role = $data['category'] === 'company' ? 'company' : 'shop';
                if (!$user->hasRole($role)) {
                    $user->assignRole($role);
                }
                $user->update(['role' => $role]);
            }
        }

        $shop->load([
            'user',
            'products' => fn ($q) => $q->with(['category', 'subcategory'])->latest()->limit(100),
            'financial',
            'orders' => fn ($q) => $q->with(['user', 'items'])->latest()->limit(200),
        ]);
        $shop->loadCount('products');
        $shop->loadCount('orders');

        return response()->json([
            'success' => true,
            'message' => 'Shop created successfully',
            'data' => $this->shopResponse($shop)
        ], 201);
    }

    /**
     * Display the specified shop with full visibility: image_url and products with images.
     */
    public function show(Shop $shop): JsonResponse
    {
        $shop->load([
            'user',
            'companyPlan',
            'products' => fn ($q) => $q->with(['category', 'subcategory'])->latest()->limit(100),
            'financial',
            'branches',
            'documents',
            'representatives.user',
            'companyProducts',
            'orders' => fn ($q) => $q->with(['user', 'items.product'])->latest()->limit(200),
        ]);
        $shop->loadCount('products');
        $shop->loadCount('orders');
        $shop->loadCount('representatives');
        $shop->loadCount('companyOrders');

        return response()->json([
            'success' => true,
            'data' => $this->shopResponse($shop)
        ]);
    }

    /**
     * Build shop response with explicit image_url, products with image URLs, and vendor dashboard summary.
     */
    private function shopResponse(Shop $shop): array
    {
        $shopArray = $shop->toArray();
        $shopArray['image_url'] = self::fullImageUrl($shop->image_url ?? null);
        $shopArray['vendor_status'] = $shop->vendor_status ?? 'approved';

        $products = $shop->relationLoaded('products') ? $shop->products : $shop->products()->with(['category', 'subcategory'])->latest()->limit(100)->get();
        $shopArray['products'] = $products->map(function (Product $product) {
            $arr = $product->toArray();
            $images = $product->images ?? [];
            $arr['images'] = is_array($images)
                ? array_map(fn ($p) => Product::makeFullImageUrl(is_string($p) ? $p : null), $images)
                : [];
            $arr['first_image_url'] = $product->first_image_url;
            $arr['image_url'] = $product->first_image_url;
            return $arr;
        })->values()->all();

        $shopArray['vendor_dashboard'] = $this->vendorDashboardSummary($shop);

        if ($shop->relationLoaded('financial') && $shop->financial) {
            $f = $shop->financial;
            $shopArray['financial'] = [
                'total_revenue' => (float) ($f->total_revenue ?? 0),
                'total_commission' => (float) ($f->total_commission ?? 0),
                'pending_balance' => (float) ($f->pending_balance ?? 0),
                'available_balance' => (float) ($f->available_balance ?? 0),
                'commission_rate' => (float) ($f->commission_rate ?? 0),
                'profit_share_percentage' => (float) $f->shop_profit_share_percentage,
            ];
        }
        if ($shop->relationLoaded('branches')) {
            $shopArray['branches'] = $shop->branches->toArray();
        } else {
            $shopArray['branches'] = $shop->branches()->orderBy('sort_order')->get()->toArray();
        }
        if ($shop->relationLoaded('documents')) {
            $shopArray['documents'] = $shop->documents->map(fn ($d) => array_merge($d->toArray(), ['file_url' => $d->file_url ? self::fullImageUrl($d->file_url) : null]))->toArray();
        } else {
            $shopArray['documents'] = $shop->documents()->get()->map(fn ($d) => array_merge($d->toArray(), ['file_url' => $d->file_url ? self::fullImageUrl($d->file_url) : null]))->toArray();
        }

        if ($shop->relationLoaded('representatives')) {
            $shopArray['representatives'] = $shop->representatives->map(fn ($r) => array_merge($r->toArray(), ['user' => $r->user]))->toArray();
        } else {
            $shopArray['representatives'] = $shop->representatives()->with('user')->get()->toArray();
        }

        $companyProducts = $shop->relationLoaded('companyProducts') ? $shop->companyProducts : $shop->companyProducts()->orderBy('sort_order')->orderBy('name')->get();
        $shopArray['company_products'] = $companyProducts->map(function (CompanyProduct $p) {
            $arr = $p->toArray();
            $arr['first_image_url'] = $p->first_image_url;
            if (!empty($arr['images']) && is_array($arr['images'])) {
                $arr['images'] = array_map(fn ($url) => CompanyProduct::makeFullImageUrl(is_string($url) ? $url : null), $arr['images']);
            }
            return $arr;
        })->values()->all();
        $shopArray['company_orders_count'] = $shop->company_orders_count ?? $shop->companyOrders()->count();

        $shopArray['is_company'] = $shop->isCompany();
        if ($shop->isCompany()) {
            $plan = $shop->relationLoaded('companyPlan') ? $shop->companyPlan : $shop->companyPlan;
            $shopArray['company_plan'] = $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'name_ar' => $plan->name_ar,
                'slug' => $plan->slug,
                'max_products' => (int) $plan->max_products,
                'max_branches' => (int) $plan->max_branches,
                'max_representatives' => (int) $plan->max_representatives,
                'price' => (float) $plan->price,
                'features' => $plan->features,
                'is_active' => $plan->is_active ?? true,
            ] : null;
            $shopArray['company_plan_usage'] = [
                'products' => count($shopArray['company_products'] ?? []),
                'branches' => count($shopArray['branches'] ?? []),
                'representatives' => $shop->representatives()->count(),
            ];
        }

        return $shopArray;
    }

    /**
     * Summary data as the vendor would see (new orders, daily sales, most sold, latest orders).
     */
    private function vendorDashboardSummary(Shop $shop): array
    {
        $today = now()->startOfDay();
        $newOrdersCount = $shop->orders()->where('created_at', '>=', $today)->count();
        $dailySales = (float) $shop->orders()->where('created_at', '>=', $today)->sum('total_amount');

        $latestOrders = $shop->orders()->with(['user', 'items.product'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (float) ($order->total_amount ?? 0),
                    'status' => $order->status,
                    'created_at' => $order->created_at?->toIso8601String(),
                    'user' => $order->user ? ['id' => $order->user->id, 'name' => $order->user->name ?? $order->user->username ?? null] : null,
                ];
            });

        $mostSold = \App\Models\OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('shop_id', $shop->id)->where('created_at', '>=', $today))
            ->selectRaw('product_id, sum(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
        $productIds = $mostSold->pluck('product_id')->filter()->unique()->values()->all();
        $mostSoldProducts = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');
        $mostSoldList = $mostSold->map(function ($row) use ($mostSoldProducts) {
            $product = $mostSoldProducts->get($row->product_id);
            return [
                'product_id' => $row->product_id,
                'quantity_sold_today' => (int) $row->total_qty,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                    'first_image_url' => $product->first_image_url,
                    'price' => (float) ($product->price ?? 0),
                ] : null,
            ];
        })->values()->all();

        return [
            'new_orders_count' => $newOrdersCount,
            'daily_sales' => round($dailySales, 2),
            'latest_orders' => $latestOrders,
            'most_sold_today' => $mostSoldList,
        ];
    }

    /**
     * Approve vendor (shop) – full access.
     */
    public function approve(Shop $shop): JsonResponse
    {
        $shop->update(['vendor_status' => Shop::VENDOR_STATUS_APPROVED, 'is_active' => true]);
        $shop->load([
            'user',
            'products' => fn ($q) => $q->with(['category', 'subcategory'])->latest()->limit(100),
            'financial',
            'orders' => fn ($q) => $q->with(['user', 'items'])->latest()->limit(200),
        ]);
        $shop->loadCount('products');
        $shop->loadCount('orders');
        return response()->json([
            'success' => true,
            'message' => 'Shop vendor approved',
            'data' => $this->shopResponse($shop)
        ]);
    }

    /**
     * Reject vendor (shop).
     */
    public function reject(Shop $shop): JsonResponse
    {
        $shop->update(['vendor_status' => Shop::VENDOR_STATUS_REJECTED, 'is_active' => false]);
        $shop->load([
            'user',
            'products' => fn ($q) => $q->with(['category', 'subcategory'])->latest()->limit(100),
            'financial',
            'orders' => fn ($q) => $q->with(['user', 'items'])->latest()->limit(200),
        ]);
        $shop->loadCount('products');
        $shop->loadCount('orders');
        return response()->json([
            'success' => true,
            'message' => 'Shop vendor rejected',
            'data' => $this->shopResponse($shop)
        ]);
    }

    /**
     * Suspend vendor (shop) – temporary block.
     */
    public function suspend(Shop $shop): JsonResponse
    {
        $shop->update(['vendor_status' => Shop::VENDOR_STATUS_SUSPENDED, 'is_active' => false]);
        $shop->load([
            'user',
            'products' => fn ($q) => $q->with(['category', 'subcategory'])->latest()->limit(100),
            'financial',
            'orders' => fn ($q) => $q->with(['user', 'items'])->latest()->limit(200),
        ]);
        $shop->loadCount('products');
        $shop->loadCount('orders');
        return response()->json([
            'success' => true,
            'message' => 'Shop vendor suspended',
            'data' => $this->shopResponse($shop)
        ]);
    }

    /**
     * Update company plan (for companies only).
     */
    public function updatePlan(Request $request, Shop $shop): JsonResponse
    {
        if (!$shop->isCompany()) {
            return response()->json(['success' => false, 'message' => 'Only companies have plans'], 422);
        }
        $validator = Validator::make($request->all(), ['company_plan_id' => 'required|exists:company_plans,id']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $plan = CompanyPlan::findOrFail($request->company_plan_id);
        if (!$plan->is_active) {
            return response()->json(['success' => false, 'message' => 'Plan is not active'], 422);
        }
        $shop->update(['company_plan_id' => $plan->id]);
        $shop->load(['user', 'companyPlan', 'representatives', 'companyProducts', 'branches']);
        $shop->loadCount('companyOrders');
        return response()->json([
            'success' => true,
            'message' => 'Company plan updated',
            'data' => $this->shopResponse($shop)
        ]);
    }

    /**
     * Update the specified shop (accepts image file or image_url).
     */
    public function update(Request $request, Shop $shop): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string',
            'subcategories' => 'nullable|array',
            'address' => 'sometimes|string',
            'phone' => 'nullable|string',
            'image_url' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'is_active' => 'sometimes|boolean',
            'vendor_status' => 'sometimes|in:'.Shop::VENDOR_STATUS_PENDING.','.Shop::VENDOR_STATUS_APPROVED.','.Shop::VENDOR_STATUS_SUSPENDED.','.Shop::VENDOR_STATUS_REJECTED,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['user_id', 'name', 'category', 'subcategories', 'address', 'phone', 'latitude', 'longitude', 'is_active', 'vendor_status', 'company_plan_id']);
        $imageUrl = $this->resolveShopImageUrl($request);
        if ($imageUrl !== null) {
            $data['image_url'] = $imageUrl;
        }

        $shop->update($data);

        if ($shop->isCompany() && $shop->vendor_status === Shop::VENDOR_STATUS_APPROVED && !$shop->is_active && !array_key_exists('is_active', $data)) {
            $shop->update(['is_active' => true]);
        }

        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            if ($user) {
                $role = ($data['category'] ?? $shop->category) === 'company' ? 'company' : 'shop';
                if (!$user->hasRole($role)) {
                    $user->assignRole($role);
                }
                $user->update(['role' => $role]);
            }
        }

        $shop->load([
            'user',
            'products' => fn ($q) => $q->with(['category', 'subcategory'])->latest()->limit(100),
            'financial',
            'orders' => fn ($q) => $q->with(['user', 'items'])->latest()->limit(200),
        ]);
        $shop->loadCount('products');
        $shop->loadCount('orders');

        return response()->json([
            'success' => true,
            'message' => 'Shop updated successfully',
            'data' => $this->shopResponse($shop)
        ]);
    }

    /**
     * Remove the specified shop
     */
    public function destroy(Shop $shop): JsonResponse
    {
        $shop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shop deleted successfully'
        ]);
    }

    /**
     * List representatives belonging to this shop (company).
     */
    public function representatives(Shop $shop): JsonResponse
    {
        $representatives = $shop->representatives()->with('user')->latest()->get();
        return response()->json(['success' => true, 'data' => $representatives]);
    }

    /**
     * Add a representative to this shop (company). Body: user_id, territory, status.
     */
    public function storeRepresentative(Request $request, Shop $shop): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'territory' => 'required|string|max:255',
            'status' => 'sometimes|in:active,pending_approval,suspended',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        if ($shop->isCompany()) {
            $plan = $shop->companyPlan;
            if ($plan && $plan->max_representatives > 0 && $shop->representatives()->count() >= $plan->max_representatives) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company plan limit reached for representatives (max: ' . $plan->max_representatives . '). Upgrade plan to add more.',
                ], 422);
            }
        }
        $data = $validator->validated();
        $data['shop_id'] = $shop->id;
        $data['employee_id'] = $data['employee_id'] ?? ('REP-' . $shop->id . '-' . $data['user_id'] . '-' . now()->format('His'));
        $rep = Representative::create($data);
        $rep->load('user');
        return response()->json(['success' => true, 'data' => $rep], 201);
    }

    /**
     * List company_orders where this shop is the customer (orders from reps).
     */
    public function ordersFromReps(Request $request, Shop $shop): JsonResponse
    {
        $query = CompanyOrder::where('customer_type', 'shop')
            ->where('customer_id', $shop->id)
            ->with(['representative.user', 'visit', 'items.companyProduct', 'customerShop', 'customerDoctor']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $orders = $query->latest()->paginate($request->per_page ?? 20);
        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * List visits done by this shop's representatives (company visits).
     */
    public function visits(Request $request, Shop $shop): JsonResponse
    {
        $visitIds = $shop->representatives()->pluck('id');
        $visits = Visit::with(['representative.user', 'shop', 'doctor'])
            ->whereIn('representative_id', $visitIds)
            ->latest()
            ->paginate($request->per_page ?? 20);
        return response()->json([
            'success' => true,
            'data' => $visits->items(),
            'pagination' => [
                'total' => $visits->total(),
                'per_page' => $visits->perPage(),
                'current_page' => $visits->currentPage(),
                'last_page' => $visits->lastPage(),
            ],
        ]);
    }

    /**
     * Full wallet details: sales, revenue, commission, profit share, transactions. Admin only.
     */
    public function wallet(Shop $shop): JsonResponse
    {
        $shop->load('financial');
        $financial = $shop->financial ?? \App\Models\ShopFinancial::firstOrCreate(['shop_id' => $shop->id]);

        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $thisYear = now()->startOfYear();

        $salesToday = (float) $shop->orders()->where('created_at', '>=', $today)->sum('total_amount');
        $salesThisMonth = (float) $shop->orders()->where('created_at', '>=', $thisMonth)->sum('total_amount');
        $salesThisYear = (float) $shop->orders()->where('created_at', '>=', $thisYear)->sum('total_amount');
        $ordersCountToday = $shop->orders()->where('created_at', '>=', $today)->count();
        $ordersCountMonth = $shop->orders()->where('created_at', '>=', $thisMonth)->count();

        $commissionRate = (float) ($financial->commission_rate ?? 10);
        $profitShare = (float) $financial->shop_profit_share_percentage;
        $totalRevenue = (float) ($financial->total_revenue ?? 0);
        $totalCommission = (float) ($financial->total_commission ?? 0);
        $shopEarnings = $totalRevenue;
        $platformCommission = $totalCommission;

        $orderTransactions = FinancialTransaction::where('shop_id', $shop->id)
            ->with('order')
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'source' => 'order',
                    'type' => $t->type ?? 'order',
                    'amount' => (float) ($t->amount ?? 0),
                    'commission' => (float) ($t->commission ?? 0),
                    'status' => $t->status ?? 'completed',
                    'order_id' => $t->order_id,
                    'order_number' => $t->order?->order_number,
                    'created_at' => $t->created_at?->toIso8601String(),
                ];
            });
        $adjustments = ShopWalletAdjustment::where('shop_id', $shop->id)
            ->with('adminUser')
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'source' => 'adjustment',
                    'type' => $a->type,
                    'amount' => (float) $a->amount,
                    'description' => $a->description,
                    'admin_user_id' => $a->admin_user_id,
                    'created_at' => $a->created_at?->toIso8601String(),
                ];
            });
        $transactions = $orderTransactions->concat($adjustments)->sortByDesc('created_at')->values()->take(100)->all();

        return response()->json([
            'success' => true,
            'data' => [
                'shop_id' => $shop->id,
                'shop_name' => $shop->name,
                'summary' => [
                    'available_balance' => (float) ($financial->available_balance ?? 0),
                    'pending_balance' => (float) ($financial->pending_balance ?? 0),
                    'total_revenue' => $totalRevenue,
                    'total_commission' => $platformCommission,
                    'commission_rate_percent' => $commissionRate,
                    'profit_share_percent' => $profitShare,
                    'shop_earnings' => $shopEarnings,
                ],
                'sales' => [
                    'today' => ['amount' => $salesToday, 'orders_count' => $ordersCountToday],
                    'this_month' => ['amount' => $salesThisMonth, 'orders_count' => $ordersCountMonth],
                    'this_year' => ['amount' => $salesThisYear],
                ],
                'transactions' => $transactions,
            ],
        ]);
    }

    /**
     * Admin: adjust shop wallet (credit/debit). Full control.
     */
    public function adjustWallet(Request $request, Shop $shop): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'description' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }
        $amount = (float) $request->amount;
        $type = $request->type;
        $description = $request->input('description', 'Admin adjustment');

        $financial = $shop->financial ?? \App\Models\ShopFinancial::firstOrCreate(['shop_id' => $shop->id]);
        $current = (float) ($financial->available_balance ?? 0);

        DB::beginTransaction();
        try {
            if ($type === 'debit') {
                if ($current < $amount) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Insufficient balance'], 422);
                }
                $financial->decrement('available_balance', $amount);
            } else {
                $financial->increment('available_balance', $amount);
            }
            $financial->refresh();
            ShopWalletAdjustment::create([
                'shop_id' => $shop->id,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'admin_user_id' => $request->user()?->id,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $type === 'credit' ? 'Credit applied' : 'Debit applied',
            'data' => [
                'available_balance' => (float) $financial->available_balance,
            ],
        ]);
    }

    /**
     * Comprehensive product reports for this shop: sales, revenue, quantities.
     */
    public function productReports(Request $request, Shop $shop): JsonResponse
    {
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->from)->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->to)->endOfDay() : now()->endOfDay();

        $orderIds = $shop->orders()->whereBetween('created_at', [$from, $to])->pluck('id');
        $items = \App\Models\OrderItem::whereIn('order_id', $orderIds)
            ->with('product')
            ->selectRaw('product_id, sum(quantity) as total_quantity, sum(total_price) as total_revenue')
            ->groupBy('product_id')
            ->get();

        $productIds = $items->pluck('product_id')->filter()->unique()->values()->all();
        $products = Product::with(['category', 'subcategory'])->whereIn('id', $productIds)->get()->keyBy('id');

        $report = $items->map(function ($row) use ($products) {
            $product = $products->get($row->product_id);
            return [
                'product_id' => $row->product_id,
                'product_name' => $product?->name,
                'product_name_ar' => $product?->name_ar,
                'first_image_url' => $product?->first_image_url,
                'category' => $product?->category?->name,
                'total_quantity_sold' => (int) $row->total_quantity,
                'total_revenue' => round((float) $row->total_revenue, 2),
            ];
        })->sortByDesc('total_revenue')->values()->all();

        $totals = [
            'products_count' => count($report),
            'total_quantity_sold' => collect($report)->sum('total_quantity_sold'),
            'total_revenue' => round(collect($report)->sum('total_revenue'), 2),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
                'totals' => $totals,
                'products' => $report,
            ],
        ]);
    }
}
