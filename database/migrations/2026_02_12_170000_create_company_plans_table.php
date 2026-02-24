<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('slug', 64)->unique();
            $table->unsignedInteger('max_products')->default(50)->comment('حد منتجات الشركة');
            $table->unsignedInteger('max_branches')->default(3)->comment('حد الفروع');
            $table->unsignedInteger('max_representatives')->default(10)->comment('حد  مندوبين المبيعات ');
            $table->decimal('price', 12, 2)->default(0);
            $table->json('features')->nullable()->comment('ميزات الخطة أو الصلاحيات');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_plans');
    }
};
