<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialty');
            // $table->string('phone')->unique();
            // $table->string('email')->unique()->nullable();
            // $table->string('password')->nullable();
            $table->string('avatar_url')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->decimal('consultation_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->json('available_days'); // ['saturday', 'sunday', ...]
            $table->time('available_hours_start');
            $table->time('available_hours_end');
            $table->text('location');
            $table->integer('consultation_duration')->default(20); // in minutes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
