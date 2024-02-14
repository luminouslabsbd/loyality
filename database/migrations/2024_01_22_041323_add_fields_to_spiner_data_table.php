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
        Schema::table('spiner_data', function (Blueprint $table) {
            $table->after('cam_id', function () use ($table) {
                $table->boolean('is_wining_label')->nullable()->default(false);
                $table->unsignedBigInteger('init_prize')->nullable()->default(0);
                $table->unsignedBigInteger('available_prize')->nullable()->default(0);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spiner_data', function (Blueprint $table) {
            $table->dropColumn('is_wining_label');
            $table->dropColumn('init_quantity');
            $table->dropColumn('available_quantity');
        });
    }
};
