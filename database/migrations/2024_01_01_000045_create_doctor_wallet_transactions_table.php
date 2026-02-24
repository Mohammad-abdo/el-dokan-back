<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['booking_payment', 'commission', 'withdrawal', 'transfer', 'refund']);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('description')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_wallet_transactions');
    }
};

