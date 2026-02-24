<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->morphs('uploadable'); // representative, doctor, admin, etc.
            $table->string('file_type'); // products, customers, visits, etc.
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_size');
            $table->string('mime_type');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};

