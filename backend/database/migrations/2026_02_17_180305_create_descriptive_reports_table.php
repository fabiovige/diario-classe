<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descriptive_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('experience_field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['student_id', 'class_group_id', 'experience_field_id', 'assessment_period_id'],
                'descriptive_reports_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descriptive_reports');
    }
};
