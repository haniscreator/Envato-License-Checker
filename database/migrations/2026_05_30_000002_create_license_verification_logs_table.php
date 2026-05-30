<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('license_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code')->index();
            $table->string('domain')->index();
            $table->string('ip_address');
            $table->enum('status', ['success', 'failed']);
            $table->text('message')->nullable();
            $table->timestamps(); // includes created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_verification_logs');
    }
};
