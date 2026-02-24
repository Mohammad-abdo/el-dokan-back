<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_prescription_id')->constrained()->onDelete('cascade');
            $table->string('medication_name');
            $table->integer('quantity');
            $table->text('notes')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_prescription_items');
    }
};

