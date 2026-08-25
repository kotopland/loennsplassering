<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_cvs', function (Blueprint $table) {
            $table->string('processing_status', 30)->nullable()->default('innsendt')->after('status');
        });

        // For existing datasets: backfill submitted records to 'innsendt'
        DB::table('employee_cvs')
            ->whereNotNull('personal_info')
            ->orWhereIn('status', ['submitted', 'generated', 'downloaded'])
            ->update(['processing_status' => 'innsendt']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_cvs', function (Blueprint $table) {
            $table->dropColumn('processing_status');
        });
    }
};
