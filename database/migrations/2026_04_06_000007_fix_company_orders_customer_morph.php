<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_orders', function (Blueprint $table) {
            $table->renameColumn('customer_type', 'customerable_type');
            $table->renameColumn('customer_id', 'customerable_id');
        });

        Schema::table('company_orders', function (Blueprint $table) {
            $table->index(['customerable_type', 'customerable_id'], 'company_orders_customerable_index');
        });
    }

    public function down(): void
    {
        Schema::table('company_orders', function (Blueprint $table) {
            $table->dropIndex('company_orders_customerable_index');
            $table->renameColumn('customerable_type', 'customer_type');
            $table->renameColumn('customerable_id', 'customer_id');
        });
    }
};
