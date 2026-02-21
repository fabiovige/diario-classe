<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('grade_levels')
            ->where('type', 'elementary')
            ->where('order', '<=', 11)
            ->update(['type' => 'elementary_early']);

        DB::table('grade_levels')
            ->where('type', 'elementary')
            ->where('order', '>', 11)
            ->update(['type' => 'elementary_late']);
    }

    public function down(): void
    {
        DB::table('grade_levels')
            ->whereIn('type', ['elementary_early', 'elementary_late'])
            ->update(['type' => 'elementary']);
    }
};
