<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->string('file_url')->nullable()->after('file_path');
        });

        Schema::table('file_uploads', function (Blueprint $table) {
            $table->string('uploadable_type')->nullable()->change();
            $table->unsignedBigInteger('uploadable_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->dropColumn('file_url');
        });

        Schema::table('file_uploads', function (Blueprint $table) {
            $table->string('uploadable_type')->nullable(false)->change();
            $table->unsignedBigInteger('uploadable_id')->nullable(false)->change();
        });
    }
};
