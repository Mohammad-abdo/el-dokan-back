<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_financials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->decimal('pending_balance', 15, 2)->default(0);
            $table->decimal('available_balance', 15, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(10.00); // percentage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_financials');
    }
};
