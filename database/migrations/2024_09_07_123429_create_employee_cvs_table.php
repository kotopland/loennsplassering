<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employee_cvs', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('job_title')->nullable();
            $table->date('work_start_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->json('education')->nullable();
            $table->json('work_experience')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_cvs');
    }
};
