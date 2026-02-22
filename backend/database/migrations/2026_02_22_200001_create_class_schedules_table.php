<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->timestamps();

            $table->unique(['teacher_assignment_id', 'time_slot_id', 'day_of_week'], 'cs_assignment_slot_day_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
