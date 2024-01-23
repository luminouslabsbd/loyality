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
        Schema::table('member_spinner_count', function (Blueprint $table) {
            $table->after('remaining_spin', function () use ($table) {
                $table->boolean('is_prizes_set')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_spinner_count', function (Blueprint $table) {
            $table->dropColumn('is_prizes_set');
        });
    }
};
