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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('vehicle_no')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('driver_phone')->nullable();
            $table->dateTime('expected_delivery_at')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('Out for Delivery'); // Out for Delivery, Delivered, Cancelled
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
