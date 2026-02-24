<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active')->after('is_active');
            $table->text('suspension_reason')->nullable()->after('status');
            $table->timestamp('suspended_at')->nullable()->after('suspension_reason');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['status', 'suspension_reason', 'suspended_at']);
        });
    }
};
