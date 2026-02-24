<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('prescription_name');
            $table->string('patient_name');
            $table->string('patient_phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('share_link')->unique()->nullable();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_template')->default(false); // Fixed prescription template
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_prescriptions');
    }
};

