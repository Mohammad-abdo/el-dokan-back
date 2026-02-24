<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pharmacy_id')->nullable()->constrained('shops')->onDelete('set null');
            $table->foreignId('pharmacist_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('images');
            $table->enum('status', ['under_review', 'dispensed', 'rejected'])->default('under_review');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};

