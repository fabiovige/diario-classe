<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('social_name')->nullable();
            $table->date('birth_date');
            $table->string('gender');
            $table->string('race_color')->default('nao_declarada');
            $table->string('cpf', 11)->unique()->nullable();
            $table->string('rg', 20)->nullable();
            $table->string('sus_number', 15)->nullable();
            $table->string('nis_number', 11)->nullable();
            $table->string('birth_city')->nullable();
            $table->string('birth_state', 2)->nullable();
            $table->string('nationality')->default('brasileira');
            $table->text('medical_notes')->nullable();
            $table->boolean('has_disability')->default(false);
            $table->string('disability_type')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
