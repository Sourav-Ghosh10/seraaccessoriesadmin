<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('app_popups', function (Blueprint $table) {
            if (!Schema::hasColumn('app_popups', 'banner_image')) {
                $table->string('banner_image')->nullable()->after('id');
            }
            if (!Schema::hasColumn('app_popups', 'image')) {
                $table->string('image')->nullable()->after('banner_image');
            }
        });

        try {
            DB::statement('ALTER TABLE app_popups MODIFY COLUMN title VARCHAR(255) NULL;');
            DB::statement('ALTER TABLE app_popups MODIFY COLUMN description TEXT NULL;');
        } catch (\Exception $e) {
            // Fallback for non-MySQL or already modified
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_popups', function (Blueprint $table) {
            if (Schema::hasColumn('app_popups', 'banner_image')) {
                $table->dropColumn('banner_image');
            }
            if (Schema::hasColumn('app_popups', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
