<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('representatives', function (Blueprint $table) {
            if (!Schema::hasColumn('representatives', 'shop_id')) {
                $table->foreignId('shop_id')->nullable()->after('user_id')->constrained('shops')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('representatives', function (Blueprint $table) {
            if (Schema::hasColumn('representatives', 'shop_id')) {
                $table->dropForeign(['shop_id']);
                $table->dropColumn('shop_id');
            }
        });
    }
};
