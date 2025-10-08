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
        Schema::table('employee_cvs', function (Blueprint $table) {
            $table->json('personal_info')->nullable()->after('birth_date')->comment('Personal information for submission');
            $table->string('status', 20)->nullable()->after('personal_info');
            $table->string('generated_file_path')->nullable()->after('status');
            $table->timestamp('generated_file_timestamp')->nullable()->after('generated_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_cvs', function (Blueprint $table) {
            $table->dropColumn(['personal_info', 'status', 'generated_file_path', 'generated_file_timestamp']);
        });
    }
};
