<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('total_users')->default(0);
            $table->integer('active_users')->default(0);
            $table->integer('total_orders')->default(0);
            $table->integer('completed_orders')->default(0);
            $table->integer('total_bookings')->default(0);
            $table->integer('completed_bookings')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->integer('total_products')->default(0);
            $table->integer('total_shops')->default(0);
            $table->integer('total_doctors')->default(0);
            $table->timestamps();

            $table->unique('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_statistics');
    }
};
