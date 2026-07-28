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
        Schema::create('salesman_attendance_unlock_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('salesman_id');
            $table->timestamp('locked_at');
            $table->timestamp('unlocked_at')->nullable();
            $table->unsignedBigInteger('unlocked_by')->nullable();
            $table->timestamps();

            $table->foreign('attendance_id')->references('id')->on('salesman_attendances')->onDelete('cascade');
            $table->foreign('salesman_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('unlocked_by')->references('id')->on('members')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesman_attendance_unlock_logs');
    }
};
