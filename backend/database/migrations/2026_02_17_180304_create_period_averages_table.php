<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_averages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->decimal('numeric_average', 5, 2)->nullable();
            $table->string('conceptual_average')->nullable();
            $table->integer('total_absences')->default(0);
            $table->decimal('frequency_percentage', 5, 2)->nullable();
            $table->timestamp('calculated_at')->nullable();

            $table->unique(
                ['student_id', 'class_group_id', 'teacher_assignment_id', 'assessment_period_id'],
                'period_averages_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_averages');
    }
};
