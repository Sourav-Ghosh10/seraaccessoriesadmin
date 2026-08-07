<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the foreign key constraint on unlocked_by in salesman_attendance_unlock_logs.
     * The unlock action is performed by an admin, not a member, so referencing members(id) is wrong.
     */
    public function up(): void
    {
        Schema::table('salesman_attendance_unlock_logs', function (Blueprint $table) {
            $table->dropForeign(['unlocked_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesman_attendance_unlock_logs', function (Blueprint $table) {
            $table->foreign('unlocked_by')->references('id')->on('members')->onDelete('set null');
        });
    }
};
