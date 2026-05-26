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
        Schema::table('estimates', function (Blueprint $table) {
            $table->string('request_number')->nullable()->unique()->after('member_id');
        });

        // Backfill existing estimates
        $estimates = DB::table('estimates')->whereNull('request_number')->get();
        foreach ($estimates as $estimate) {
            DB::table('estimates')
                ->where('id', $estimate->id)
                ->update([
                    'request_number' => 'EST-' . str_pad($estimate->id, 4, '0', STR_PAD_LEFT)
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('request_number');
        });
    }
};
