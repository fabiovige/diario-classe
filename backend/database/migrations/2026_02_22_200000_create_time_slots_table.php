<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('number');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('type', 10)->default('class');
            $table->timestamps();

            $table->unique(['shift_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
