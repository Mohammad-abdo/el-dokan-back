<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');
            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->string('form')->nullable(); // tablets, etc.
            $table->integer('quantity')->nullable();
            $table->integer('duration')->nullable(); // in days
            $table->text('instructions')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_medications');
    }
};

