<?php

namespace Database\Seeders;

use App\Modules\Attendance\Domain\Entities\AbsenceJustification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsenceJustificationSeeder extends Seeder
{
    private const REASONS = [
        'Consulta médica',
        'Doença na família',
        'Problema de saúde',
        'Acompanhamento médico especializado',
        'Motivos familiares',
    ];

    private const START_DATE = '2026-03-01';

    private const END_DATE = '2026-03-03';

    private const APPROVED_AT = '2026-03-10';

    public function run(): void
    {
        $admin = \App\Models\User::where('email', 'admin@jandira.sp.gov.br')->first();

        $absentStudentIds = DB::table('attendance_records')
            ->where('status', 'absent')
            ->distinct()
            ->pluck('student_id')
            ->toArray();

        $limit = min(50, (int) ceil(count($absentStudentIds) * 0.2));
        $selectedStudentIds = array_slice($absentStudentIds, 0, $limit);

        foreach ($selectedStudentIds as $studentId) {
            $approved = (bool) random_int(0, 1);

            AbsenceJustification::create([
                'student_id' => $studentId,
                'start_date' => self::START_DATE,
                'end_date' => self::END_DATE,
                'reason' => self::REASONS[array_rand(self::REASONS)],
                'document_path' => null,
                'approved' => $approved,
                'approved_by' => $approved ? $admin?->id : null,
                'approved_at' => $approved ? self::APPROVED_AT : null,
                'created_by' => $admin?->id,
            ]);
        }
    }
}
