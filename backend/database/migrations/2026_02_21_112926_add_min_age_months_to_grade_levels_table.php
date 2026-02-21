<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_age_months')->nullable()->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->dropColumn('min_age_months');
        });
    }
};
