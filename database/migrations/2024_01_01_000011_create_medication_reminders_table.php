<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prescription_medication_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('medication_name');
            $table->time('reminder_time');
            $table->enum('time_period', ['am', 'pm']);
            $table->enum('frequency', ['twice_daily', 'three_times_daily', 'daily', 'specific_days']);
            $table->json('specific_days')->nullable(); // for specific_days frequency
            $table->enum('duration', ['week', 'two_weeks', 'three_weeks', 'month', 'specific_period']);
            $table->integer('duration_days')->nullable(); // for specific_period
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_reminders');
    }
};

