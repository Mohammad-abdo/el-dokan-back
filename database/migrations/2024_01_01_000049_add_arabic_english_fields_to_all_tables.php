<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add name_ar, name_en to shops
        if (!Schema::hasColumn('shops', 'name_ar')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_en')->nullable()->after('name_ar');
            });
        }

        // Add name_ar, name_en, description_ar, description_en to products
        if (!Schema::hasColumn('products', 'name_ar')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_en')->nullable()->after('name_ar');
                $table->text('description_ar')->nullable()->after('description');
                $table->text('description_en')->nullable()->after('description_ar');
            });
        }

        // Add name_ar, name_en to doctors
        if (!Schema::hasColumn('doctors', 'name_ar')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_en')->nullable()->after('name_ar');
                $table->string('specialty_ar')->nullable()->after('specialty');
                $table->string('specialty_en')->nullable()->after('specialty_ar');
                $table->text('location_ar')->nullable()->after('location');
                $table->text('location_en')->nullable()->after('location_ar');
            });
        }

        // Add title_ar, title_en to addresses
        if (!Schema::hasColumn('addresses', 'title_ar')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->string('title_ar')->nullable()->after('title');
                $table->string('title_en')->nullable()->after('title_ar');
                $table->string('city_ar')->nullable()->after('city');
                $table->string('city_en')->nullable()->after('city_ar');
                $table->string('district_ar')->nullable()->after('district');
                $table->string('district_en')->nullable()->after('district_ar');
            });
        }

        // Add name_ar, name_en to medical_centers
        if (!Schema::hasColumn('medical_centers', 'name_ar')) {
            Schema::table('medical_centers', function (Blueprint $table) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_en')->nullable()->after('name_ar');
                $table->text('address_ar')->nullable()->after('address');
                $table->text('address_en')->nullable()->after('address_ar');
            });
        }

        // Add title_ar, title_en, description_ar, description_en to sliders
        if (!Schema::hasColumn('sliders', 'title_ar')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->string('title_ar')->nullable()->after('title');
                $table->string('title_en')->nullable()->after('title_ar');
                $table->text('description_ar')->nullable()->after('description');
                $table->text('description_en')->nullable()->after('description_ar');
            });
        }
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en']);
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'specialty_ar', 'specialty_en', 'location_ar', 'location_en']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_en', 'city_ar', 'city_en', 'district_ar', 'district_en']);
        });

        Schema::table('medical_centers', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'address_ar', 'address_en']);
        });

        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_en', 'description_ar', 'description_en']);
        });
    }
};

