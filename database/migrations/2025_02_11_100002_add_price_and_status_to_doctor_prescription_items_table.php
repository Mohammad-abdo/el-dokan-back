<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_prescription_items', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0)->after('quantity');
            $table->string('status', 32)->default('pending')->after('price'); // pending, in_cart, completed
        });
    }

    public function down(): void
    {
        Schema::table('doctor_prescription_items', function (Blueprint $table) {
            $table->dropColumn(['price', 'status']);
        });
    }
};
