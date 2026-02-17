<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->smallInteger('number');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('open');
            $table->timestamps();

            $table->unique(['academic_year_id', 'type', 'number'], 'assessment_periods_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_periods');
    }
};
