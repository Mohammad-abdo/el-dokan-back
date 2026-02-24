<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        // إعطاء المتاجر الحالية إحداثيات افتراضية (القاهرة) حتى يعمل التتبع
        $baseLat = 30.0444;
        $baseLng = 31.2357;
        $shops = DB::table('shops')->whereNull('latitude')->get();
        foreach ($shops as $i => $shop) {
            DB::table('shops')->where('id', $shop->id)->update([
                'latitude' => $baseLat + ($i + 1) * 0.008,
                'longitude' => $baseLng + ($i + 1) * 0.008,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
