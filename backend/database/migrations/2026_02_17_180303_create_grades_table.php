<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_instrument_id')->constrained()->cascadeOnDelete();
            $table->decimal('numeric_value', 5, 2)->nullable();
            $table->string('conceptual_value')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('is_recovery')->default(false);
            $table->string('recovery_type')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'assessment_period_id']);
            $table->index(['class_group_id', 'teacher_assignment_id', 'assessment_period_id'], 'grades_class_assignment_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
