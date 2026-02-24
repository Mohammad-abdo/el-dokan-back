<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('description_en');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('slug')->nullable()->unique()->after('name');
            $table->text('short_description')->nullable()->after('description');
            $table->text('short_description_ar')->nullable()->after('description_ar');
            $table->text('short_description_en')->nullable()->after('description_en');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title',
                'meta_description',
                'meta_keywords',
                'slug',
                'short_description',
                'short_description_ar',
                'short_description_en',
            ]);
        });
    }
};




