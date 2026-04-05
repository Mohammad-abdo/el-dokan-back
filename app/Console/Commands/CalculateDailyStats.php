<?php

namespace App\Console\Commands;

use App\Models\ApplicationStatistic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateDailyStats extends Command
{
    protected $signature = 'stats:calculate-daily';
    protected $description = 'Calculate and store daily application statistics';

    public function handle(): int
    {
        $this->info('Calculating daily statistics...');

        $today = today();

        $totalRevenue = (float) DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        ApplicationStatistic::updateOrCreate(
            ['date' => $today],
            [
                'total_users'        => DB::table('users')->whereNull('deleted_at')->count(),
                'active_users'       => DB::table('users')->whereNull('deleted_at')->where('status', 'active')->count(),
                'total_orders'       => DB::table('orders')->whereNull('deleted_at')->count(),
                'completed_orders'   => DB::table('orders')->whereNull('deleted_at')->where('status', 'delivered')->count(),
                'total_bookings'     => DB::table('bookings')->count(),
                'completed_bookings' => DB::table('bookings')->where('status', 'completed')->count(),
                'total_revenue'      => $totalRevenue,
                'total_commission'   => $totalRevenue * 0.05,
                'total_products'     => DB::table('products')->whereNull('deleted_at')->count(),
                'total_shops'        => DB::table('shops')->count(),
                'total_doctors'      => DB::table('doctors')->count(),
            ]
        );

        $this->info('Daily statistics calculated successfully.');
        return self::SUCCESS;
    }
}
