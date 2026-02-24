<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    /**
     * Get orders report
     */
    public function ordersReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'orders' => $orders,
                'total_orders' => $orders->sum('count'),
                'total_revenue' => $orders->sum('total'),
            ]
        ]);
    }

    /**
     * Get financial report
     */
    public function financialReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $transactions = FinancialTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'), DB::raw('sum(commission) as commission'))
            ->groupBy('type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'transactions' => $transactions,
                'total_revenue' => $transactions->sum('total'),
                'total_commission' => $transactions->sum('commission'),
            ]
        ]);
    }

    /**
     * Get users report
     */
    public function usersReport(Request $request): JsonResponse
    {
        $users = User::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $newUsers = User::whereDate('created_at', '>=', now()->subDays(30))->count();

        return response()->json([
            'success' => true,
            'data' => [
                'by_status' => $users,
                'total_users' => $users->sum('count'),
                'new_users_30_days' => $newUsers,
            ]
        ]);
    }

    /**
     * Get products report
     */
    public function productsReport(): JsonResponse
    {
        $products = Product::select('is_active', DB::raw('count(*) as count'))
            ->groupBy('is_active')
            ->get();

        $lowStock = Product::where('stock_quantity', '<', 10)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'by_status' => $products,
                'total_products' => $products->sum('count'),
                'low_stock_count' => $lowStock,
            ]
        ]);
    }

    /**
     * Get companies report — شركات مع خططها وحالاتها وإحصائيات.
     */
    public function companiesReport(): JsonResponse
    {
        $shops = Shop::where(function ($q) {
            $q->where('category', 'company')->orWhereHas('user', fn ($u) => $u->where('role', 'company'));
        })
            ->with('companyPlan')
            ->withCount(['companyProducts', 'representatives', 'companyOrders'])
            ->get();

        $companies = $shops->map(function (Shop $shop) {
            $revenue = (float) $shop->companyOrders()->whereIn('status', ['completed', 'delivered', 'confirmed'])->sum('total_amount');
            $plan = $shop->companyPlan;
            return [
                'id' => $shop->id,
                'name' => $shop->name,
                'vendor_status' => $shop->vendor_status ?? 'approved',
                'plan' => $plan ? [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'name_ar' => $plan->name_ar,
                ] : null,
                'plan_name' => $plan ? ($plan->name_ar ?: $plan->name) : null,
                'company_products_count' => $shop->company_products_count ?? 0,
                'representatives_count' => $shop->representatives_count ?? 0,
                'company_orders_count' => $shop->company_orders_count ?? 0,
                'revenue' => $revenue,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
                'total_companies' => $companies->count(),
                'by_status' => $companies->groupBy('vendor_status')->map->count(),
            ],
        ]);
    }

    /**
     * Get dashboard report
     */
    public function dashboardReport(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => User::count(),
                'total_orders' => Order::count(),
                'total_products' => Product::count(),
                'total_revenue' => FinancialTransaction::where('status', 'completed')->sum('amount'),
                'pending_orders' => Order::where('status', 'received')->count(),
                'active_products' => Product::where('is_active', true)->count(),
            ]
        ]);
    }

    /**
     * Export report as file (CSV or JSON)
     * GET /admin/reports/{type}/export?format=csv|json&start_date=&end_date=
     */
    public function export(Request $request, string $type): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
    {
        $allowedTypes = ['orders', 'financial', 'users', 'products', 'dashboard', 'companies'];
        if (!in_array($type, $allowedTypes)) {
            return response()->json(['success' => false, 'message' => 'Invalid report type'], 422);
        }

        $format = $request->get('format', 'json');
        if (!in_array($format, ['json', 'csv'])) {
            $format = 'json';
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $data = null;
        switch ($type) {
            case 'orders':
                $data = $this->ordersReport($request)->getData(true);
                break;
            case 'financial':
                $data = $this->financialReport($request)->getData(true);
                break;
            case 'users':
                $data = $this->usersReport($request)->getData(true);
                break;
            case 'products':
                $data = $this->productsReport()->getData(true);
                break;
            case 'dashboard':
                $data = $this->dashboardReport()->getData(true);
                break;
            case 'companies':
                $data = $this->companiesReport()->getData(true);
                break;
        }

        $filename = $type === 'companies'
            ? "{$type}-report-" . now()->toDateString() . ".{$format}"
            : "{$type}-report-{$startDate}-to-{$endDate}.{$format}";

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($data) {
                $out = fopen('php://output', 'w');
                $this->writeReportCsv($out, $data);
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv',
            ]);
        }

        return response()->streamDownload(
            function () use ($data) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            },
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    private function writeReportCsv($handle, array $data): void
    {
        $payload = $data['data'] ?? [];
        if (!is_array($payload)) {
            return;
        }
        foreach ($payload as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                fputcsv($handle, array_keys((array) $value[0]));
                foreach ($value as $row) {
                    fputcsv($handle, (array) $row);
                }
                return;
            }
        }
        fputcsv($handle, ['Key', 'Value']);
        foreach ($payload as $k => $v) {
            if (!is_array($v)) {
                fputcsv($handle, [$k, $v]);
            }
        }
    }
}
