<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = [
            'Manhã' => 'morning',
            'Manha' => 'morning',
            'Tarde' => 'afternoon',
            'Noite' => 'evening',
            'Integral' => 'full_time',
        ];

        foreach ($mapping as $old => $new) {
            DB::table('shifts')->where('name', $old)->update(['name' => $new]);
        }
    }

    public function down(): void
    {
        $mapping = [
            'morning' => 'Manhã',
            'afternoon' => 'Tarde',
            'evening' => 'Noite',
            'full_time' => 'Integral',
        ];

        foreach ($mapping as $old => $new) {
            DB::table('shifts')->where('name', $old)->update(['name' => $new]);
        }
    }
};
