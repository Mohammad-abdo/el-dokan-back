<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'name_ar')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('name_ar')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'name_ar')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('name_ar');
            });
        }
    }
};


