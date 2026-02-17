<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_config_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->decimal('max_value', 5, 2)->nullable();
            $table->smallInteger('order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_instruments');
    }
};
