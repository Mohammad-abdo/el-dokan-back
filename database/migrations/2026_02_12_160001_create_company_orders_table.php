<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * مبيعات الشركة: عندما يبيع المندوب منتجات الشركة لمتجر أو طبيب (مرتبط بالزيارة).
     */
    public function up(): void
    {
        Schema::create('company_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->nullable();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade')->comment('الشركة البائعة');
            $table->foreignId('representative_id')->constrained()->onDelete('cascade');
            $table->foreignId('visit_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_type'); // shop, doctor
            $table->unsignedBigInteger('customer_id')->comment('shop_id or doctor_id');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('pending')->comment('pending, confirmed, delivered, cancelled');
            $table->text('notes')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('company_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_order_items');
        Schema::dropIfExists('company_orders');
    }
};
