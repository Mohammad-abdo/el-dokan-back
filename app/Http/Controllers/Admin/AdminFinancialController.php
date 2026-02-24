<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FinancialService;
use App\Models\FinancialTransaction;
use App\Models\ShopFinancial;
use App\Models\ApplicationStatistic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminFinancialController extends Controller
{
    protected FinancialService $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Get financial dashboard
     */
    public function dashboard(): JsonResponse
    {
        try {
            $data = $this->financialService->getDashboardData();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching financial dashboard data',
                'error' => $e->getMessage(),
                'data' => [
                    'today' => ['revenue' => 0, 'orders' => 0],
                    'this_month' => ['revenue' => 0, 'orders' => 0, 'commission' => 0],
                    'total' => ['revenue' => 0, 'commission' => 0],
                ]
            ], 500);
        }
    }

    /**
     * Get financial transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $query = FinancialTransaction::with(['order', 'shop', 'user']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Get shop financials
     */
    public function shopFinancials(): JsonResponse
    {
        $shopFinancials = ShopFinancial::with('shop')
            ->orderByDesc(DB::raw('COALESCE(total_revenue, 0)'))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $shopFinancials
        ]);
    }

    /**
     * Get all vendors financial data (shops, doctors, drivers, representatives)
     */
    public function vendorsFinancials(): JsonResponse
    {
        try {
            // Shops financials (shop may be null if deleted) - table has total_revenue, available_balance
            $shops = ShopFinancial::with('shop')
                ->orderByDesc(DB::raw('COALESCE(total_revenue, 0)'))
                ->get()
                ->map(function ($shopFinancial) {
                    $shop = $shopFinancial->shop;
                    return [
                        'id' => $shopFinancial->shop_id,
                        'type' => 'shop',
                        'name' => $shop?->name ?? 'N/A',
                        'balance' => (float) ($shopFinancial->available_balance ?? $shopFinancial->total_revenue ?? 0),
                        'total_orders' => $shop ? $shop->orders()->count() : 0,
                        'total_transactions' => $shop ? $shop->orders()->count() : 0,
                    ];
                });

            // Doctors wallets (only those with wallet)
            $doctors = \App\Models\Doctor::with('wallet')
                ->whereHas('wallet')
                ->get()
                ->map(function ($doctor) {
                    $wallet = $doctor->wallet;
                    return [
                        'id' => $doctor->id,
                        'type' => 'doctor',
                        'name' => $doctor->name ?? $doctor->user?->name ?? 'N/A',
                        'balance' => (float) ($wallet->balance ?? 0),
                        'total_orders' => (int) ($doctor->bookings()->count()),
                        'total_transactions' => (int) ($wallet->transactions()->count()),
                    ];
                });

            // Drivers (user may be null)
            $drivers = \App\Models\Driver::with('user')
                ->get()
                ->map(function ($driver) {
                    $user = $driver->user;
                    return [
                        'id' => $driver->id,
                        'type' => 'driver',
                        'name' => $driver->name ?? $user?->name ?? 'N/A',
                        'balance' => (float) ($user?->wallet_balance ?? 0),
                        'total_orders' => (int) (\App\Models\Delivery::where('driver_id', $driver->id)->count()),
                        'total_transactions' => 0,
                    ];
                });

            // Representatives (user may be null)
            $representatives = \App\Models\Representative::with('user')
                ->get()
                ->map(function ($representative) {
                    $user = $representative->user;
                    return [
                        'id' => $representative->id,
                        'type' => 'representative',
                        'name' => $user?->username ?? $user?->email ?? 'N/A',
                        'balance' => (float) ($user?->wallet_balance ?? 0),
                        'total_orders' => (int) ($representative->visits()->count()),
                        'total_transactions' => 0,
                    ];
                });

            $shopsBalance = $shops->sum('balance');
            $doctorsBalance = $doctors->sum('balance');
            $driversBalance = $drivers->sum('balance');
            $repsBalance = $representatives->sum('balance');

            return response()->json([
                'success' => true,
                'data' => [
                    'shops' => $shops,
                    'doctors' => $doctors,
                    'drivers' => $drivers,
                    'representatives' => $representatives,
                    'total_vendors' => $shops->count() + $doctors->count() + $drivers->count() + $representatives->count(),
                    'total_balance' => $shopsBalance + $doctorsBalance + $driversBalance + $repsBalance,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vendors financial data',
                'error' => $e->getMessage(),
                'data' => [
                    'shops' => [],
                    'doctors' => [],
                    'drivers' => [],
                    'representatives' => [],
                    'total_vendors' => 0,
                    'total_balance' => 0,
                ]
            ], 500);
        }
    }

    /**
     * Get vendor wallet details
     */
    public function vendorWallet(Request $request): JsonResponse
    {
        $type = $request->get('type'); // shop, doctor, driver, representative
        $id = $request->get('id');

        if (!$type || !$id) {
            return response()->json([
                'success' => false,
                'message' => 'Type and ID are required'
            ], 400);
        }

        $data = [];

        switch ($type) {
            case 'shop':
                $shop = \App\Models\Shop::with(['financial', 'products', 'orders'])->find($id);
                if ($shop) {
                    $financial = $shop->financial;
                    $data = [
                        'id' => $shop->id,
                        'type' => 'shop',
                        'name' => $shop->name,
                        'balance' => $financial ? ($financial->available_balance ?? $financial->total_revenue ?? 0) : 0,
                        'total_products' => $shop->products()->count(),
                        'total_orders' => $shop->orders()->count(),
                        'transactions' => FinancialTransaction::where('shop_id', $shop->id)
                            ->latest()
                            ->limit(50)
                            ->get(),
                    ];
                }
                break;

            case 'doctor':
                $doctor = \App\Models\Doctor::with(['wallet.transactions', 'bookings'])->find($id);
                if ($doctor) {
                    $wallet = $doctor->wallet;
                    $data = [
                        'id' => $doctor->id,
                        'type' => 'doctor',
                        'name' => $doctor->name,
                        'balance' => $wallet ? ($wallet->balance ?? 0) : 0,
                        'total_bookings' => $doctor->bookings()->count() ?? 0,
                        'transactions' => $wallet ? ($wallet->transactions()->latest()->limit(50)->get() ?? []) : [],
                    ];
                }
                break;

            case 'driver':
                $driver = \App\Models\Driver::with(['user', 'deliveries'])->find($id);
                if ($driver) {
                    $data = [
                        'id' => $driver->id,
                        'type' => 'driver',
                        'name' => $driver->name,
                        'balance' => $driver->user->wallet_balance ?? 0,
                        'total_deliveries' => $driver->deliveries()->count() ?? 0,
                        'transactions' => [], // Can be added if driver transactions table exists
                    ];
                }
                break;

            case 'representative':
                $representative = \App\Models\Representative::with(['user', 'visits'])->find($id);
                if ($representative) {
                    $data = [
                        'id' => $representative->id,
                        'type' => 'representative',
                        'name' => $representative->user->username ?? 'N/A',
                        'balance' => $representative->user->wallet_balance ?? 0,
                        'total_visits' => $representative->visits()->count(),
                        'transactions' => [],
                    ];
                }
                break;
        }

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get financial statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // day, week, month, year

        $query = ApplicationStatistic::query();

        switch ($period) {
            case 'day':
                $query->whereDate('date', now()->toDateString());
                break;
            case 'week':
                $query->where('date', '>=', now()->startOfWeek()->toDateString());
                break;
            case 'month':
                $query->where('date', '>=', now()->startOfMonth()->toDateString());
                break;
            case 'year':
                $query->where('date', '>=', now()->startOfYear()->toDateString());
                break;
        }

        $stats = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'statistics' => $stats,
                'totals' => [
                    'revenue' => $stats->sum('total_revenue'),
                    'commission' => $stats->sum('total_commission'),
                    'orders' => $stats->sum('total_orders'),
                ]
            ]
        ]);
    }
}
