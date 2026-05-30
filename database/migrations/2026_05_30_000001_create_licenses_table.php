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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code')->unique()->index();
            $table->string('domain')->index();
            $table->string('buyer_username');
            $table->string('item_id');
            $table->string('item_name');
            $table->string('license_type');
            $table->timestamp('purchase_date');
            $table->enum('status', ['active', 'revoked'])->default('active');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
