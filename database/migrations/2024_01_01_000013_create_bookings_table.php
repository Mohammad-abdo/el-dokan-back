<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->enum('booking_type', ['online', 'in_clinic']);
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->string('patient_name');
            $table->enum('status', ['upcoming', 'in_progress', 'completed', 'cancelled'])->default('upcoming');
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['credit_card', 'e_wallet', 'cash_on_delivery']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->integer('rating')->nullable();
            $table->text('complaint')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

