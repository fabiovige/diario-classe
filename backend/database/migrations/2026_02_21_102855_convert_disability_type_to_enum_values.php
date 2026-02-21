<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MAPPING = [
        'Visual' => 'visual',
        'Auditiva' => 'hearing',
        'Física' => 'physical',
        'Intelectual' => 'intellectual',
        'TEA' => 'autism',
        'Altas Habilidades' => 'gifted_talented',
    ];

    public function up(): void
    {
        foreach (self::MAPPING as $old => $new) {
            DB::table('students')
                ->where('disability_type', $old)
                ->update(['disability_type' => $new]);
        }
    }

    public function down(): void
    {
        $reverse = array_flip(self::MAPPING);
        foreach ($reverse as $current => $original) {
            DB::table('students')
                ->where('disability_type', $current)
                ->update(['disability_type' => $original]);
        }
    }
};
