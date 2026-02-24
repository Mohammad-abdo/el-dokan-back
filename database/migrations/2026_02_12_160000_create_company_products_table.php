<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * منتجات الشركة (أدوية، تراكيب، منتجات أخرى) - منفصلة تماماً عن منتجات المتاجر.
     */
    public function up(): void
    {
        Schema::create('company_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade')->comment('الشركة = المتجر من نوع company');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('product_type')->default('other')->comment('drug, compound, other');
            $table->string('unit')->default('piece');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_products');
    }
};
