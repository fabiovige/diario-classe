<?php

namespace Database\Seeders;

use App\Modules\ClassRecord\Domain\Entities\LessonRecord;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use Illuminate\Database\Seeder;

class LessonRecordSeeder extends Seeder
{
    private const LESSON_DATES = [
        '2025-02-17', '2025-02-24', '2025-03-03', '2025-03-10',
        '2025-03-17', '2025-03-24', '2025-03-31', '2025-04-07',
        '2025-04-28', '2025-05-05', '2025-05-12', '2025-05-19',
        '2025-05-26', '2025-06-02', '2025-06-09', '2025-06-16',
        '2025-08-04', '2025-08-11', '2025-08-18', '2025-08-25',
        '2025-09-01', '2025-09-08', '2025-09-15', '2025-09-22',
        '2025-10-06', '2025-10-13', '2025-10-20', '2025-10-27',
        '2025-11-03', '2025-11-10', '2025-11-17', '2025-11-24',
    ];

    private const CONTENTS = [
        'Introdução ao conteúdo programático do bimestre',
        'Desenvolvimento de atividades práticas em grupo',
        'Avaliação diagnóstica e revisão de conteúdos anteriores',
        'Trabalho em grupo com apresentação oral',
        'Atividades de fixação e exercícios dirigidos',
        'Revisão geral e preparação para avaliação',
        'Leitura compartilhada e interpretação de textos',
        'Atividades de pesquisa e produção textual',
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
    ];

    public function run(): void
    {
        $classGroupIds = ClassAssignment::where('status', 'active')
            ->distinct()
            ->pluck('class_group_id');

        foreach ($classGroupIds as $classGroupId) {
            $teacherAssignments = TeacherAssignment::where('class_group_id', $classGroupId)
                ->where('active', true)
                ->with('teacher')
                ->get();

            foreach ($teacherAssignments as $teacherAssignment) {
                $recordedBy = $teacherAssignment->teacher?->user_id;

                foreach (self::LESSON_DATES as $index => $date) {
                    LessonRecord::updateOrCreate(
                        [
                            'class_group_id' => $classGroupId,
                            'teacher_assignment_id' => $teacherAssignment->id,
                            'date' => $date,
                        ],
                        [
                            'content' => self::CONTENTS[$index % count(self::CONTENTS)],
                            'methodology' => self::METHODOLOGIES[$index % count(self::METHODOLOGIES)],
                            'observations' => null,
                            'class_count' => 2,
                            'recorded_by' => $recordedBy,
                        ],
                    );
                }
            }
        }
    }
}
