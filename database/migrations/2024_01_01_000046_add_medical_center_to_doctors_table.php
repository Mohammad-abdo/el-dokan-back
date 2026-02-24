<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->foreignId('primary_medical_center_id')->nullable()->after('location')->constrained('medical_centers')->onDelete('set null');
            $table->string('default_password')->default('123456')->after('consultation_duration');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['primary_medical_center_id']);
            $table->dropColumn(['primary_medical_center_id', 'default_password']);
        });
    }
};

