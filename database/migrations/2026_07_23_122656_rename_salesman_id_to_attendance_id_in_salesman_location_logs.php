<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Replaces salesman_id (FK → members.id) with attendance_id (FK → salesman_attendances.id).
     *
     * Steps:
     *  1. Add the new attendance_id column (nullable for now).
     *  2. Backfill: for each log row, find the salesman_attendance record that matches
     *     the same member and the same calendar date as the log's timestamp.
     *  3. Drop the old foreign key + salesman_id column.
     *  4. Make attendance_id non-nullable (optional — left nullable for safety in case
     *     old rows have no matching attendance record).
     */
    public function up(): void
    {
        // ── Step 1: Add new column + FK while salesman_id still exists ────────
        Schema::table('salesman_location_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_id')->nullable()->after('id');
            $table->foreign('attendance_id')
                  ->references('id')
                  ->on('salesman_attendances')
                  ->onDelete('cascade');
        });

        // ── Step 2: Backfill attendance_id ────────────────────────────────────
        DB::statement("
            UPDATE salesman_location_logs sll
            JOIN salesman_attendances sa
                ON  sa.member_id = sll.salesman_id
                AND DATE(sa.date) = DATE(sll.timestamp)
            SET sll.attendance_id = sa.id
        ");

        // ── Step 3: Drop old FK + column ──────────────────────────────────────
        Schema::table('salesman_location_logs', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->dropColumn('salesman_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add salesman_id (nullable, best-effort reverse)
        Schema::table('salesman_location_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('salesman_id')->nullable()->after('id');
            $table->foreign('salesman_id')
                  ->references('id')
                  ->on('members')
                  ->onDelete('cascade');
        });

        // Best-effort backfill salesman_id from the attendance record
        DB::statement("
            UPDATE salesman_location_logs sll
            JOIN salesman_attendances sa ON sa.id = sll.attendance_id
            SET sll.salesman_id = sa.member_id
        ");

        // Drop new column + FK
        Schema::table('salesman_location_logs', function (Blueprint $table) {
            $table->dropForeign(['attendance_id']);
            $table->dropColumn('attendance_id');
        });
    }
};
