<?php

namespace Database\Seeders;

use App\Modules\ClassRecord\Domain\Entities\LessonRecord;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use Illuminate\Database\Seeder;

class LessonRecordSeeder extends Seeder
{
    private const LESSON_DATES = [
        '2026-02-16',
        '2026-02-23',
        '2026-03-02',
        '2026-04-27',
        '2026-05-04',
        '2026-05-11',
    ];

    private const CONTENTS = [
        'Introdução ao conteúdo programático do bimestre',
        'Desenvolvimento de atividades práticas em grupo',
        'Avaliação diagnóstica e revisão de conteúdos anteriores',
        'Trabalho em grupo com apresentação oral',
        'Atividades de fixação e exercícios dirigidos',
        'Revisão geral e preparação para avaliação',
    ];

    private const METHODOLOGIES = [
        'Aula expositiva dialogada com recursos audiovisuais',
        'Dinâmica em grupo com material concreto',
        'Roda de conversa e debate orientado',
        'Atividade prática com resolução de problemas',
        'Trabalho individual com acompanhamento do professor',
        'Jogos educativos e atividades lúdicas',
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
