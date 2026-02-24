<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_prescription_items', function (Blueprint $table) {
            if (!Schema::hasColumn('doctor_prescription_items', 'dosage')) {
                $table->string('dosage')->nullable()->after('medication_name');
            }
            if (!Schema::hasColumn('doctor_prescription_items', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('doctor_prescription_items', 'instructions')) {
                $table->text('instructions')->nullable()->after('duration_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctor_prescription_items', function (Blueprint $table) {
            $table->dropColumn(['dosage', 'duration_days', 'instructions']);
        });
    }
};
