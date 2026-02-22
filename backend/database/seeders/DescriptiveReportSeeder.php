<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DescriptiveReportSeeder extends Seeder
{
    private const BATCH_SIZE = 5000;

    private const REPORT_TEMPLATES = [
        'O aluno demonstra bom desenvolvimento nas atividades propostas, participando ativamente das rodas de conversa e interagindo com os colegas de forma respeitosa.',
        'Apresenta evolução significativa na coordenação motora e nas atividades artísticas. Mostra interesse em explorar diferentes materiais e texturas.',
        'Tem se destacado na oralidade, expressando suas ideias com clareza. Participa com entusiasmo das brincadeiras e jogos coletivos.',
        'Demonstra curiosidade e interesse pelas atividades de exploração do ambiente. Realiza as atividades com autonomia crescente.',
        'O aluno está em processo de adaptação, apresentando progresso gradual na socialização e nas atividades pedagógicas propostas.',
        'Apresenta excelente desempenho na resolução de situações-problema e nas atividades que envolvem raciocínio lógico.',
        'Demonstra criatividade e expressividade nas atividades de artes visuais e música. Interage bem com os colegas durante as atividades em grupo.',
        'Tem apresentado avanços na construção da escrita e no reconhecimento de letras e números. Participa ativamente das atividades de contação de histórias.',
        'O aluno mostra interesse e envolvimento nas atividades de movimento corporal. Demonstra respeito às regras dos jogos e brincadeiras.',
        'Apresenta desenvolvimento adequado para a faixa etária, com boa interação social e participação nas atividades propostas pelo educador.',
    ];

    public function run(): void
    {
        DB::disableQueryLog();

        $infantilIds = DB::table('grade_levels')
            ->where('type', 'early_childhood')
            ->pluck('id')
            ->toArray();

        $experienceFieldIds = DB::table('experience_fields')
            ->where('active', true)
            ->pluck('id')
            ->toArray();

        $classGroups = DB::table('class_groups')
            ->whereIn('grade_level_id', $infantilIds)
            ->whereIn('id', function ($q) {
                $q->select('class_group_id')
                    ->from('class_assignments')
                    ->where('status', 'active')
                    ->distinct();
            })
            ->get();

        $firstPeriods = DB::table('assessment_periods')
            ->where('number', 1)
            ->pluck('id', 'academic_year_id');

        $teacherAssignments = DB::table('teacher_assignments')
            ->where('teacher_assignments.active', true)
            ->whereIn('teacher_assignments.class_group_id', $classGroups->pluck('id'))
            ->join('teachers', 'teachers.id', '=', 'teacher_assignments.teacher_id')
            ->select('teacher_assignments.class_group_id', 'teachers.user_id')
            ->get()
            ->unique('class_group_id')
            ->keyBy('class_group_id');

        $studentsByClass = DB::table('class_assignments')
            ->where('class_assignments.status', 'active')
            ->whereIn('class_assignments.class_group_id', $classGroups->pluck('id'))
            ->join('enrollments', 'enrollments.id', '=', 'class_assignments.enrollment_id')
            ->select('class_assignments.class_group_id', 'enrollments.student_id')
            ->get()
            ->groupBy('class_group_id');

        $templateCount = count(self::REPORT_TEMPLATES);
        $templateIndex = 0;
        $now = now()->toDateTimeString();
        $batch = [];
        $total = $classGroups->count();

        foreach ($classGroups as $index => $cg) {
            $periodId = $firstPeriods->get($cg->academic_year_id);
            $ta = $teacherAssignments->get($cg->id);
            $students = $studentsByClass->get($cg->id);

            if (! $periodId || ! $ta || ! $students) {
                continue;
            }

            $recordedBy = $ta->user_id;

            foreach ($students as $student) {
                foreach ($experienceFieldIds as $fieldId) {
                    $batch[] = [
                        'student_id' => $student->student_id,
                        'class_group_id' => $cg->id,
                        'experience_field_id' => $fieldId,
                        'assessment_period_id' => $periodId,
                        'content' => self::REPORT_TEMPLATES[$templateIndex % $templateCount],
                        'recorded_by' => $recordedBy,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $templateIndex++;

                    if (count($batch) >= self::BATCH_SIZE) {
                        DB::table('descriptive_reports')->insert($batch);
                        $batch = [];
                    }
                }
            }

            if (($index + 1) % 100 === 0) {
                $this->command->info("  DescriptiveReports: " . ($index + 1) . "/$total turmas...");
            }
        }

        if (! empty($batch)) {
            DB::table('descriptive_reports')->insert($batch);
        }

        $this->command->info("  DescriptiveReports: $total/$total turmas finalizadas.");
    }
}
