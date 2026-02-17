<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('result');
            $table->decimal('overall_average', 5, 2)->nullable();
            $table->decimal('overall_frequency', 5, 2)->nullable();
            $table->boolean('council_override')->default(false);
            $table->text('observations')->nullable();
            $table->foreignId('determined_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['student_id', 'class_group_id', 'academic_year_id'],
                'final_results_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_results');
    }
};
