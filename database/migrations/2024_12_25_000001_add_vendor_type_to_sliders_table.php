<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            if (!Schema::hasColumn('sliders', 'vendor_type')) {
                $table->string('vendor_type')->nullable()->after('link_type'); // shop, doctor, driver, representative, general
            }
            if (!Schema::hasColumn('sliders', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('vendor_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            if (Schema::hasColumn('sliders', 'vendor_type')) {
                $table->dropColumn('vendor_type');
            }
            if (Schema::hasColumn('sliders', 'vendor_id')) {
                $table->dropColumn('vendor_id');
            }
        });
    }
};

