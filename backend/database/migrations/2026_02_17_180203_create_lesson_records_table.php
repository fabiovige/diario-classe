<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_assignment_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('content');
            $table->text('methodology')->nullable();
            $table->text('observations')->nullable();
            $table->smallInteger('class_count')->default(1);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['class_group_id', 'teacher_assignment_id', 'date'],
                'lesson_records_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_records');
    }
};
