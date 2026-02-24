<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->text('store_address');
            $table->text('delivery_address');
            $table->integer('estimated_arrival_minutes')->nullable();
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed'])->default('pending');
            $table->string('qr_code')->nullable();
            $table->integer('rating')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};

