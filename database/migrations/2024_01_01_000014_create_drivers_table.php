<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo_url')->nullable();
            $table->string('phone')->unique();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->decimal('current_location_lat', 10, 8)->nullable();
            $table->decimal('current_location_lng', 11, 8)->nullable();
            $table->enum('status', ['available', 'busy', 'offline'])->default('available');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};

