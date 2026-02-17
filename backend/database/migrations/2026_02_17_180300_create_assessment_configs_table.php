<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->string('grade_type');
            $table->decimal('scale_min', 5, 2)->nullable();
            $table->decimal('scale_max', 5, 2)->nullable();
            $table->decimal('passing_grade', 5, 2)->nullable();
            $table->string('average_formula')->default('arithmetic');
            $table->smallInteger('rounding_precision')->default(1);
            $table->boolean('recovery_enabled')->default(true);
            $table->string('recovery_replaces')->default('higher');
            $table->timestamps();

            $table->unique(['school_id', 'academic_year_id', 'grade_level_id'], 'assessment_configs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_configs');
    }
};
