<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year');
            $table->string('status')->default('planning');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->unique(['school_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
