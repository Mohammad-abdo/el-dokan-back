<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            
            $table->index(['phone', 'otp', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};

