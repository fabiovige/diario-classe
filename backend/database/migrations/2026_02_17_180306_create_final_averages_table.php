<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_averages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->decimal('numeric_average', 5, 2)->nullable();
            $table->decimal('recovery_grade', 5, 2)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->integer('total_absences')->default(0);
            $table->decimal('frequency_percentage', 5, 2)->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(
                ['student_id', 'class_group_id', 'teacher_assignment_id', 'academic_year_id'],
                'final_averages_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_averages');
    }
};
