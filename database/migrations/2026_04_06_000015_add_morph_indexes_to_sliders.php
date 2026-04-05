<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->index(['vendor_id', 'vendor_type'], 'sliders_vendor_morph_index');
            $table->index(['link_id', 'link_type'], 'sliders_link_morph_index');
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropIndex('sliders_vendor_morph_index');
            $table->dropIndex('sliders_link_morph_index');
        });
    }
};
