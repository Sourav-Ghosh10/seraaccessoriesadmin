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
        Schema::table('app_popups', function (Blueprint $table) {
            if (Schema::hasColumn('app_popups', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('app_popups', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_popups', function (Blueprint $table) {
            if (!Schema::hasColumn('app_popups', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (!Schema::hasColumn('app_popups', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });
    }
};
