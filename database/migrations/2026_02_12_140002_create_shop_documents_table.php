<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('type'); // permit, license, tax_card, commercial_register, etc.
            $table->string('title')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('file_url')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_documents');
    }
};
