<?php

namespace Database\Seeders;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\DescriptiveReport;
use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DescriptiveReportSeeder extends Seeder
{
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
        $infantilIds = GradeLevel::where('type', 'early_childhood')->pluck('id')->toArray();

        $experienceFields = ExperienceField::where('active', true)->get();

        $classGroupIds = ClassAssignment::where('status', 'active')
            ->distinct()
            ->pluck('class_group_id')
            ->toArray();

        $classGroups = ClassGroup::whereIn('id', $classGroupIds)
            ->whereIn('grade_level_id', $infantilIds)
            ->with('academicYear')
            ->get();

        $templateCount = count(self::REPORT_TEMPLATES);
        $templateIndex = 0;

        foreach ($classGroups as $classGroup) {
            $studentIds = DB::table('class_assignments')
                ->join('enrollments', 'enrollments.id', '=', 'class_assignments.enrollment_id')
                ->where('class_assignments.class_group_id', $classGroup->id)
                ->where('class_assignments.status', 'active')
                ->pluck('enrollments.student_id')
                ->toArray();

            $periods = AssessmentPeriod::where('academic_year_id', $classGroup->academic_year_id)
                ->whereIn('number', [1, 2])
                ->get();

            $firstTeacherAssignment = TeacherAssignment::where('class_group_id', $classGroup->id)
                ->where('active', true)
                ->with('teacher')
                ->first();

            if (! $firstTeacherAssignment) {
                continue;
            }

            $recordedBy = $firstTeacherAssignment->teacher->user_id;

            foreach ($studentIds as $studentId) {
                foreach ($experienceFields as $experienceField) {
                    foreach ($periods as $period) {
                        $content = self::REPORT_TEMPLATES[$templateIndex % $templateCount];
                        $templateIndex++;

                        DescriptiveReport::updateOrCreate(
                            [
                                'student_id' => $studentId,
                                'class_group_id' => $classGroup->id,
                                'experience_field_id' => $experienceField->id,
                                'assessment_period_id' => $period->id,
                            ],
                            [
                                'content' => $content,
                                'recorded_by' => $recordedBy,
                            ],
                        );
                    }
                }
            }
        }
    }
}
