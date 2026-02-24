<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            if (!Schema::hasColumn('sliders', 'link_url')) {
                $table->string('link_url', 500)->nullable()->after('link_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            if (Schema::hasColumn('sliders', 'link_url')) {
                $table->dropColumn('link_url');
            }
        });
    }
};
