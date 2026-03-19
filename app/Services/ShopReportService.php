<?php

namespace App\Services;

use App\Models\CompanyOrder;
use App\Models\FinancialTransaction;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopDocument;
use App\Models\ShopFinancial;
use App\Models\ShopWalletAdjustment;
use App\Models\Visit;
use Carbon\Carbon;

class ShopReportService
{
    private const ALLOWED_SECTIONS = [
        'overview',
        'products',
        'wallet',
        'ordersFromReps',
        'visits',
        'representatives',
        'companyOrders',
        'branches',
        'documents',
    ];

    public function generateFullReport(Shop $shop, ?string $from, ?string $to): array
    {
        return $this->buildReport($shop, self::ALLOWED_SECTIONS, $from, $to);
    }

    public function generateCustomReport(Shop $shop, array $sections, ?string $from, ?string $to): array
    {
        $sections = array_intersect($sections, self::ALLOWED_SECTIONS);
        return $this->buildReport($shop, $sections, $from, $to);
    }

    private function buildReport(Shop $shop, array $sections, ?string $from, ?string $to): array
    {
        $dateFrom = $from ?? now()->startOfMonth()->toDateString();
        $dateTo = $to ?? now()->toDateString();

        $fromDt = Carbon::parse($dateFrom)->startOfDay();
        $toDt = Carbon::parse($dateTo)->endOfDay();

        $report = [
            'shop' => $this->getShopInfo($shop),
            'kpis' => $this->getShopKPIs($shop, $fromDt, $toDt),
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'sections' => [],
            'generated_at' => now()->toDateTimeString(),
        ];

        foreach ($sections as $section) {
            $report['sections'][$section] = match ($section) {
                'overview' => $this->getOverview($shop, $fromDt, $toDt),
                'products' => $this->getProducts($shop, $fromDt, $toDt),
                'wallet' => $this->getWallet($shop, $fromDt, $toDt),
                'ordersFromReps' => $this->getOrdersFromReps($shop, $fromDt, $toDt),
                'visits' => $this->getVisits($shop, $fromDt, $toDt),
                'representatives' => $this->getRepresentatives($shop),
                'companyOrders' => $this->getCompanyOrders($shop, $fromDt, $toDt),
                'branches' => $this->getBranches($shop),
                'documents' => $this->getDocuments($shop),
                default => null,
            };
        }

        return $report;
    }

    private function fullImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') return $path;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;

        $base = rtrim(config('app.url'), '/');
        return $base . (str_starts_with($path, '/') ? $path : '/' . $path);
    }

    private function getShopInfo(Shop $shop): array
    {
        return [
            'id' => $shop->id,
            'name' => $shop->name ?? '-',
            'category' => $shop->category,
            'address' => $shop->address ?? '-',
            'phone' => $shop->phone ?? '-',
            // Ensure absolute URL (important for DomPDF image fetching).
            'image_url' => $shop->image_url ? $this->fullImageUrl($shop->image_url) : null,
            'vendor_status' => $shop->vendor_status ?? 'approved',
            'is_active' => $shop->is_active ?? true,
            'is_company' => $shop->isCompany(),
            'created_at' => $shop->created_at?->toDateString(),
            'user' => $shop->user ? [
                'id' => $shop->user->id,
                'username' => $shop->user->username,
                'email' => $shop->user->email,
                'phone' => $shop->user->phone,
            ] : null,
            'company_plan' => $shop->companyPlan ? [
                'id' => $shop->companyPlan->id,
                'name' => $shop->companyPlan->name,
                'name_ar' => $shop->companyPlan->name_ar ?? null,
                'slug' => $shop->companyPlan->slug,
                'max_products' => (int) $shop->companyPlan->max_products,
                'max_branches' => (int) $shop->companyPlan->max_branches,
                'max_representatives' => (int) $shop->companyPlan->max_representatives,
                'price' => (float) ($shop->companyPlan->price ?? 0),
                'is_active' => (bool) ($shop->companyPlan->is_active ?? true),
            ] : null,
        ];
    }

    private function getShopKPIs(Shop $shop, Carbon $fromDt, Carbon $toDt): array
    {
        $ordersCount = (int) $shop->orders()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->count();

        $revenue = (float) $shop->orders()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->sum('total_amount');

        $visitsCount = 0;
        if ($shop->isCompany()) {
            $repIds = $shop->representatives()->pluck('id')->filter()->values()->all();
            if (!empty($repIds)) {
                $visitsCount = (int) Visit::whereIn('representative_id', $repIds)
                    ->whereBetween('visit_date', [$fromDt->toDateString(), $toDt->toDateString()])
                    ->count();
            }
        }

        return [
            'orders_count' => $ordersCount,
            'total_revenue' => round($revenue, 2),
            'visits_count' => $visitsCount,
            'status' => 'ready',
        ];
    }

    private function getOverview(Shop $shop, Carbon $fromDt, Carbon $toDt): array
    {
        $ordersCount = (int) $shop->orders()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->count();

        $revenue = (float) $shop->orders()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->sum('total_amount');

        $productsCount = (int) $shop->products()->count();
        $representativesCount = (int) $shop->representatives()->count();
        $branchesCount = (int) $shop->branches()->count();
        $documentsCount = (int) $shop->documents()->count();

        $visitsCount = 0;
        if ($shop->isCompany()) {
            $repIds = $shop->representatives()->pluck('id')->filter()->values()->all();
            if (!empty($repIds)) {
                $visitsCount = (int) Visit::whereIn('representative_id', $repIds)
                    ->whereBetween('visit_date', [$fromDt->toDateString(), $toDt->toDateString()])
                    ->count();
            }
        }

        return [
            'period' => [
                'from' => $fromDt->toDateString(),
                'to' => $toDt->toDateString(),
            ],
            'summary' => [
                'products_count' => $productsCount,
                'orders_count' => $ordersCount,
                'revenue' => round($revenue, 2),
                'representatives_count' => $representativesCount,
                'visits_count' => $visitsCount,
                'branches_count' => $branchesCount,
                'documents_count' => $documentsCount,
            ],
        ];
    }

    private function getProducts(Shop $shop, Carbon $fromDt, Carbon $toDt): array
    {
        $orderIds = $shop->orders()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            return [
                'period' => [
                    'from' => $fromDt->toDateString(),
                    'to' => $toDt->toDateString(),
                ],
                'totals' => [
                    'products_count' => 0,
                    'total_quantity_sold' => 0,
                    'total_revenue' => 0,
                ],
                'products' => [],
            ];
        }

        $items = OrderItem::whereIn('order_id', $orderIds)
            ->with('product.category', 'product.subcategory')
            ->selectRaw('product_id, sum(quantity) as total_quantity, sum(total_price) as total_revenue')
            ->groupBy('product_id')
            ->get();

        $productIds = $items->pluck('product_id')->filter()->unique()->values()->all();
        $products = Product::with(['category', 'subcategory'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $report = $items->map(function ($row) use ($products) {
            $product = $products->get($row->product_id);

            return [
                'product_id' => $row->product_id,
                'product_name' => $product?->name ?? null,
                'product_name_ar' => $product?->name_ar ?? null,
                // DomPDF is sensitive to relative/invalid URLs.
                'first_image_url' => $this->fullImageUrl($product?->first_image_url ?? null),
                'category' => $product?->category?->name ?? null,
                'total_quantity_sold' => (int) ($row->total_quantity ?? 0),
                'total_revenue' => round((float) ($row->total_revenue ?? 0), 2),
            ];
        })->sortByDesc('total_revenue')->values()->all();

        $totalQuantity = array_sum(array_map(fn ($p) => (int) ($p['total_quantity_sold'] ?? 0), $report));
        $totalRevenue = round(array_sum(array_map(fn ($p) => (float) ($p['total_revenue'] ?? 0), $report)), 2);

        return [
            'period' => [
                'from' => $fromDt->toDateString(),
                'to' => $toDt->toDateString(),
            ],
            'totals' => [
                'products_count' => count($report),
                'total_quantity_sold' => $totalQuantity,
                'total_revenue' => $totalRevenue,
            ],
            'products' => $report,
        ];
    }

    private function getWallet(Shop $shop, Carbon $fromDt, Carbon $toDt): array
    {
        $financial = $shop->financial
            ?? ShopFinancial::firstOrCreate(['shop_id' => $shop->id]);

        $financial->refresh();

        $ordersInRangeRevenue = (float) $shop->orders()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->sum('total_amount');

        $orderTransactions = FinancialTransaction::where('shop_id', $shop->id)
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->with('order')
            ->latest('created_at')
            ->limit(250)
            ->get();

        $adjustments = ShopWalletAdjustment::where('shop_id', $shop->id)
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->with('adminUser')
            ->latest('created_at')
            ->limit(250)
            ->get();

        $transactions = $orderTransactions
            ->map(function (FinancialTransaction $t) {
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
            })
            ->concat(
                $adjustments->map(function (ShopWalletAdjustment $a) {
                    return [
                        'id' => $a->id,
                        'source' => 'adjustment',
                        'type' => $a->type,
                        'amount' => (float) ($a->amount ?? 0),
                        'description' => $a->description,
                        'admin_user_id' => $a->admin_user_id,
                        'created_at' => $a->created_at?->toIso8601String(),
                    ];
                })
            )
            ->sortByDesc('created_at')
            ->values()
            ->all();

        return [
            'balance' => (float) ($financial->available_balance ?? 0),
            'pending_balance' => (float) ($financial->pending_balance ?? 0),
            'total_revenue' => round($ordersInRangeRevenue, 2),
            'total_commission' => (float) ($financial->total_commission ?? 0),
            'commission_rate' => (float) ($financial->commission_rate ?? 0),
            'commission_profit_share' => (float) ($financial->shop_profit_share_percentage ?? 0),
            'transactions_count' => count($transactions),
            'transactions' => $transactions,
        ];
    }

    private function getOrdersFromReps(Shop $shop, Carbon $fromDt, Carbon $toDt): array
    {
        $orders = CompanyOrder::where('customer_type', CompanyOrder::CUSTOMER_TYPE_SHOP)
            ->where('customer_id', $shop->id)
            ->whereBetween('ordered_at', [$fromDt, $toDt])
            ->with(['representative.user', 'visit', 'items.companyProduct', 'customerShop', 'customerDoctor'])
            ->latest('ordered_at')
            ->limit(2000)
            ->get();

        $byStatus = $orders->groupBy('status')->map->count()->all();

        return [
            'total' => $orders->count(),
            'by_status' => $byStatus,
            'orders' => $orders->toArray(),
        ];
    }

    private function getVisits(Shop $shop, Carbon $fromDt, Carbon $toDt): array
    {
        if (!$shop->isCompany()) {
            return [
                'total' => 0,
                'by_status' => [],
                'visits' => [],
            ];
        }

        $repIds = $shop->representatives()->pluck('id')->filter()->values()->all();
        if (empty($repIds)) {
            return [
                'total' => 0,
                'by_status' => [],
                'visits' => [],
            ];
        }

        $visits = Visit::with(['representative.user', 'shop', 'doctor'])
            ->whereIn('representative_id', $repIds)
            ->whereBetween('visit_date', [$fromDt->toDateString(), $toDt->toDateString()])
            ->latest('visit_date')
            ->limit(2000)
            ->get();

        $byStatus = $visits->groupBy('status')->map->count()->all();

        return [
            'total' => $visits->count(),
            'by_status' => $byStatus,
            'visits' => $visits->toArray(),
        ];
    }

    private function getRepresentatives(Shop $shop): array
    {
        $representatives = $shop->representatives()
            ->with('user')
            ->latest()
            ->get();

        return [
            'total' => $representatives->count(),
            'representatives' => $representatives->toArray(),
        ];
    }

    private function getCompanyOrders(Shop $shop, Carbon $fromDt, Carbon $toDt): array
    {
        if (!$shop->isCompany()) {
            return [
                'total' => 0,
                'by_status' => [],
                'orders' => [],
            ];
        }

        $orders = CompanyOrder::where('shop_id', $shop->id)
            ->whereBetween('ordered_at', [$fromDt, $toDt])
            ->with(['representative.user', 'visit', 'items.companyProduct', 'customerShop', 'customerDoctor'])
            ->latest('ordered_at')
            ->limit(2000)
            ->get();

        $byStatus = $orders->groupBy('status')->map->count()->all();

        return [
            'total' => $orders->count(),
            'by_status' => $byStatus,
            'orders' => $orders->toArray(),
        ];
    }

    private function getBranches(Shop $shop): array
    {
        return [
            'branches' => $shop->branches()->orderBy('sort_order')->get()->toArray(),
        ];
    }

    private function getDocuments(Shop $shop): array
    {
        $documents = $shop->documents()->get();

        $result = $documents->map(function (ShopDocument $doc) {
            $arr = $doc->toArray();
            $arr['file_url'] = $doc->file_url ? $this->fullImageUrl($doc->file_url) : null;
            return $arr;
        })->values()->all();

        return [
            'documents' => $result,
        ];
    }
}

