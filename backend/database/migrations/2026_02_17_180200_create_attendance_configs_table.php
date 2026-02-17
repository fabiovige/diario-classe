<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('consecutive_absences_alert')->default(5);
            $table->smallInteger('monthly_absences_alert')->default(10);
            $table->decimal('period_absence_percentage_alert', 5, 2)->default(25.00);
            $table->decimal('annual_minimum_frequency', 5, 2)->default(75.00);
            $table->timestamps();

            $table->unique(['school_id', 'academic_year_id'], 'attendance_configs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_configs');
    }
};
