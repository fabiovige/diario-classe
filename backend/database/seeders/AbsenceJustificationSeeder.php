<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsenceJustificationSeeder extends Seeder
{
    private const BATCH_SIZE = 1000;

    private const BIMESTRE_1_START = '2025-02-10';

    private const BIMESTRE_1_END = '2025-04-17';

    private const REASONS = [
        'Consulta médica',
        'Doença na família',
        'Problema de saúde',
        'Acompanhamento médico especializado',
        'Motivos familiares',
    ];

    public function run(): void
    {
        $adminId = DB::table('users')
            ->where('email', 'admin@jandira.sp.gov.br')
            ->value('id');

        $absentRecords = DB::table('attendance_records')
            ->where('status', 'absent')
            ->whereBetween('date', [self::BIMESTRE_1_START, self::BIMESTRE_1_END])
            ->select('student_id', DB::raw('MIN(date) as first_absence'), DB::raw('COUNT(*) as total'))
            ->groupBy('student_id')
            ->having('total', '>=', 3)
            ->orderByDesc('total')
            ->limit(100)
            ->get();

        $batch = [];
        $reasons = self::REASONS;
        $reasonCount = count($reasons);

        foreach ($absentRecords as $index => $record) {
            $startDate = $record->first_absence;
            $endDate = date('Y-m-d', strtotime($startDate . ' +2 days'));
            $approved = $index % 3 !== 0;

            $batch[] = [
                'student_id' => $record->student_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $reasons[$index % $reasonCount],
                'document_path' => null,
                'approved' => $approved,
                'approved_by' => $approved ? $adminId : null,
                'approved_at' => $approved ? date('Y-m-d', strtotime($endDate . ' +7 days')) : null,
                'created_by' => $adminId,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                DB::table('absence_justifications')->insert($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('absence_justifications')->insert($batch);
        }

        $this->command->info("  AbsenceJustifications: " . count($absentRecords) . " justificativas criadas.");
    }
}
