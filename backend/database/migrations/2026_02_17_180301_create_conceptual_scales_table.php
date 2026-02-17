<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conceptual_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_config_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('label');
            $table->decimal('numeric_equivalent', 5, 2);
            $table->boolean('passing');
            $table->smallInteger('order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conceptual_scales');
    }
};
