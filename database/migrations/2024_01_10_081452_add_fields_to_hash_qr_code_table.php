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
        Schema::table('hash_qr_code', function (Blueprint $table) {
            $table->after('product_id', function () use ($table) {
                $table->string('order_id')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hash_qr_code', function (Blueprint $table) {
            $table->string('order_id')->nullable();
        });
    }
};
