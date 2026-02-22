<?php

namespace Database\Seeders;

use Database\Seeders\Traits\GeneratesSchoolDays;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonRecordSeeder extends Seeder
{
    use GeneratesSchoolDays;

    private const BATCH_SIZE = 5000;

    private const BIMESTRE_1_START = '2025-02-10';

    private const BIMESTRE_1_END = '2025-04-17';

    private const CONTENTS = [
        'Introdução ao conteúdo programático do bimestre',
        'Desenvolvimento de atividades práticas em grupo',
        'Avaliação diagnóstica e revisão de conteúdos anteriores',
        'Trabalho em grupo com apresentação oral',
        'Atividades de fixação e exercícios dirigidos',
        'Revisão geral e preparação para avaliação',
        'Leitura compartilhada e interpretação de textos',
        'Atividades de pesquisa e produção textual',
        'Resolução de situações-problema contextualizadas',
        'Projeto interdisciplinar com produção coletiva',
        'Atividades de campo e observação do meio',
        'Seminário com apresentação dos alunos',
    ];

    private const METHODOLOGIES = [
        'Aula expositiva dialogada com recursos audiovisuais',
        'Dinâmica em grupo com material concreto',
        'Roda de conversa e debate orientado',
        'Atividade prática com resolução de problemas',
        'Trabalho individual com acompanhamento do professor',
        'Jogos educativos e atividades lúdicas',
        'Pesquisa orientada com uso de tecnologia',
        'Atividade interdisciplinar com produção coletiva',
        'Leitura compartilhada com mediação do professor',
        'Trabalho cooperativo em pequenos grupos',
        'Experimentação e registro de observações',
        'Produção textual orientada com revisão entre pares',
    ];

    public function run(): void
    {
        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET UNIQUE_CHECKS=0');

        $schoolDays = $this->generateSchoolDays(2025, self::BIMESTRE_1_START, self::BIMESTRE_1_END);
        $contentCount = count(self::CONTENTS);
        $methodCount = count(self::METHODOLOGIES);
        $now = now()->toDateTimeString();

        $classGroups = DB::table('class_assignments')
            ->where('status', 'active')
            ->distinct()
            ->pluck('class_group_id')
            ->toArray();

        $teacherAssignments = DB::table('teacher_assignments')
            ->where('teacher_assignments.active', true)
            ->whereIn('teacher_assignments.class_group_id', $classGroups)
            ->join('teachers', 'teachers.id', '=', 'teacher_assignments.teacher_id')
            ->select('teacher_assignments.id', 'teacher_assignments.class_group_id', 'teachers.user_id')
            ->get()
            ->groupBy('class_group_id');

        $total = count($classGroups);
        $batch = [];
        $globalIndex = 0;

        foreach ($classGroups as $index => $classGroupId) {
            $tas = $teacherAssignments->get($classGroupId);

            if (! $tas) {
                continue;
            }

            foreach ($tas as $ta) {
                foreach ($schoolDays as $date) {
                    $batch[] = [
                        'class_group_id' => $classGroupId,
                        'teacher_assignment_id' => $ta->id,
                        'date' => $date,
                        'content' => self::CONTENTS[$globalIndex % $contentCount],
                        'methodology' => self::METHODOLOGIES[$globalIndex % $methodCount],
                        'observations' => null,
                        'class_count' => rand(1, 2) === 1 ? 2 : 1,
                        'recorded_by' => $ta->user_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $globalIndex++;

                    if (count($batch) >= self::BATCH_SIZE) {
                        DB::table('lesson_records')->insert($batch);
                        $batch = [];
                    }
                }
            }

            if (($index + 1) % 200 === 0) {
                $this->command->info("  LessonRecords: " . ($index + 1) . "/$total turmas...");
            }
        }

        if (! empty($batch)) {
            DB::table('lesson_records')->insert($batch);
        }

        DB::statement('SET UNIQUE_CHECKS=1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info("  LessonRecords: $total/$total turmas finalizadas.");
    }
}
