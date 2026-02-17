<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('all_grades_complete')->default(false);
            $table->boolean('all_attendance_complete')->default(false);
            $table->boolean('all_lesson_records_complete')->default(false);
            $table->timestamps();

            $table->unique(
                ['class_group_id', 'teacher_assignment_id', 'assessment_period_id'],
                'period_closings_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_closings');
    }
};
