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
        Schema::table('redeem_request', function (Blueprint $table) {
            $table->string('dealer_file_path')->nullable()->after('status');
            $table->string('distributor_file_path')->nullable()->after('dealer_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redeem_request', function (Blueprint $table) {
            $table->dropColumn(['dealer_file_path', 'distributor_file_path']);
        });
    }
};
