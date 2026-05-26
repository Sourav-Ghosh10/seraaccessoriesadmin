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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile')->nullable();
            $table->string('password');
            $table->string('role'); // dealer, salesman, distributor
            $table->string('status')->default('Active');
            
            // Dealer specific fields
            $table->string('shop')->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('salesman_id')->nullable(); 

            // Salesman specific fields
            $table->string('emp_id')->nullable();
            $table->string('ref_code')->nullable();

            $table->timestamps();
            
            // Self-referencing foreign key for dealer -> salesman
            $table->foreign('salesman_id')->references('id')->on('members')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
