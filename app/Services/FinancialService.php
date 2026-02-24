<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\ShopFinancial;
use App\Models\ApplicationStatistic;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Process order payment and create financial transactions
     */
    public function processOrderPayment(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Calculate commission (default 10%)
            $commissionRate = 0.10;
            $commission = $order->total_amount * $commissionRate;
            $shopAmount = $order->total_amount - $commission;

            // Create financial transaction
            FinancialTransaction::create([
                'order_id' => $order->id,
                'shop_id' => $order->shop_id,
                'user_id' => $order->user_id,
                'type' => 'order',
                'amount' => $order->total_amount,
                'commission' => $commission,
                'status' => 'completed',
            ]);

            // Update shop financials (table has total_revenue, available_balance)
            $shopFinancial = ShopFinancial::firstOrCreate(['shop_id' => $order->shop_id]);
            $shopFinancial->increment('total_revenue', $shopAmount);
            $shopFinancial->increment('available_balance', $shopAmount);
            $shopFinancial->increment('total_commission', $commission);

            // Update application statistics
            $this->updateStatistics('order', $order->total_amount, $commission);
        });
    }

    /**
     * Update application statistics
     */
    public function updateStatistics(string $type, float $amount, float $commission = 0): void
    {
        $stat = ApplicationStatistic::firstOrCreate(['date' => now()->toDateString()]);

        switch ($type) {
            case 'order':
                $stat->increment('total_orders');
                $stat->increment('total_revenue', $amount);
                $stat->increment('total_commission', $commission);
                break;
            case 'withdrawal':
                $stat->increment('total_withdrawals', $amount);
                break;
        }
    }

    /**
     * Get financial dashboard data
     */
    public function getDashboardData(): array
    {
        try {
            $today = now()->toDateString();
            $thisMonth = now()->startOfMonth();

            // Check if FinancialTransaction table exists
            $hasFinancialTransactions = DB::getSchemaBuilder()->hasTable('financial_transactions');
            $hasOrders = DB::getSchemaBuilder()->hasTable('orders');

            $todayRevenue = 0;
            $todayOrders = 0;
            $monthRevenue = 0;
            $monthOrders = 0;
            $monthCommission = 0;
            $totalRevenue = 0;
            $totalCommission = 0;

            if ($hasFinancialTransactions) {
                $todayRevenue = FinancialTransaction::whereDate('created_at', $today)
                    ->where('status', 'completed')
                    ->sum('amount') ?? 0;
                
                $monthRevenue = FinancialTransaction::where('created_at', '>=', $thisMonth)
                    ->where('status', 'completed')
                    ->sum('amount') ?? 0;
                
                $monthCommission = FinancialTransaction::where('created_at', '>=', $thisMonth)
                    ->where('status', 'completed')
                    ->sum('commission') ?? 0;
                
                $totalRevenue = FinancialTransaction::where('status', 'completed')->sum('amount') ?? 0;
                $totalCommission = FinancialTransaction::where('status', 'completed')->sum('commission') ?? 0;
            }

            if ($hasOrders) {
                $todayOrders = Order::whereDate('created_at', $today)->count();
                $monthOrders = Order::where('created_at', '>=', $thisMonth)->count();
            }

            return [
                'today' => [
                    'revenue' => (float) $todayRevenue,
                    'orders' => (int) $todayOrders,
                ],
                'this_month' => [
                    'revenue' => (float) $monthRevenue,
                    'orders' => (int) $monthOrders,
                    'commission' => (float) $monthCommission,
                ],
                'total' => [
                    'revenue' => (float) $totalRevenue,
                    'commission' => (float) $totalCommission,
                ],
            ];
        } catch (\Exception $e) {
            // Return empty data if there's an error
            return [
                'today' => [
                    'revenue' => 0,
                    'orders' => 0,
                ],
                'this_month' => [
                    'revenue' => 0,
                    'orders' => 0,
                    'commission' => 0,
                ],
                'total' => [
                    'revenue' => 0,
                    'commission' => 0,
                ],
            ];
        }
    }
}