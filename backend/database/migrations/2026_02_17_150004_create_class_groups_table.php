<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('max_students')->default(30);
            $table->timestamps();

            $table->unique(['academic_year_id', 'grade_level_id', 'shift_id', 'name'], 'class_groups_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};
