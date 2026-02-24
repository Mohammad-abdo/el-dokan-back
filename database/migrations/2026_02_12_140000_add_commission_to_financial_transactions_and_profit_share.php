<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('financial_transactions') && !Schema::hasColumn('financial_transactions', 'commission')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->decimal('commission', 15, 2)->nullable()->after('amount');
            });
        }
        if (Schema::hasTable('shop_financials') && !Schema::hasColumn('shop_financials', 'profit_share_percentage')) {
            Schema::table('shop_financials', function (Blueprint $table) {
                $table->decimal('profit_share_percentage', 5, 2)->nullable()->after('commission_rate')->comment('نسبة الدكان من الأرباح');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('financial_transactions', 'commission')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->dropColumn('commission');
            });
        }
        if (Schema::hasColumn('shop_financials', 'profit_share_percentage')) {
            Schema::table('shop_financials', function (Blueprint $table) {
                $table->dropColumn('profit_share_percentage');
            });
        }
    }
};
