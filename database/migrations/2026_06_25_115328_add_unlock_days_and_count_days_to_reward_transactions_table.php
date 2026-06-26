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
        Schema::table('reward_transactions', function (Blueprint $table) {
            $table->integer('unlock_days')->nullable();
            $table->integer('count_days')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_transactions', function (Blueprint $table) {
            $table->dropColumn(['unlock_days', 'count_days']);
        });
    }
};
