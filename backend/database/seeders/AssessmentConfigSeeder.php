<?php

namespace Database\Seeders;

use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\AssessmentInstrument;
use App\Modules\Assessment\Domain\Entities\ConceptualScale;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Domain\Enums\GradeLevelType;
use Illuminate\Database\Seeder;

class AssessmentConfigSeeder extends Seeder
{
    private const INSTRUMENTS = [
        ['name' => 'Prova', 'weight' => 3.00, 'max_value' => 10.00, 'order' => 1],
        ['name' => 'Trabalho', 'weight' => 2.00, 'max_value' => 10.00, 'order' => 2],
        ['name' => 'Participação', 'weight' => 1.00, 'max_value' => 10.00, 'order' => 3],
    ];

    private const CONCEPTUAL_SCALES = [
        ['code' => 'PS', 'label' => 'Plenamente Satisfatório', 'numeric_equivalent' => 9.00, 'passing' => true, 'order' => 1],
        ['code' => 'S', 'label' => 'Satisfatório', 'numeric_equivalent' => 7.00, 'passing' => true, 'order' => 2],
        ['code' => 'NS', 'label' => 'Não Satisfatório', 'numeric_equivalent' => 4.00, 'passing' => false, 'order' => 3],
    ];

    public function run(): void
    {
        $gradeLevels = GradeLevel::all()->keyBy('id');

        $academicYears = AcademicYear::all();

        foreach ($academicYears as $academicYear) {
            $gradeLevelIds = ClassGroup::where('academic_year_id', $academicYear->id)
                ->distinct()
                ->pluck('grade_level_id');

            foreach ($gradeLevelIds as $gradeLevelId) {
                $gradeLevel = $gradeLevels->get($gradeLevelId);

                if (! $gradeLevel) {
                    continue;
                }

                $configData = $this->resolveConfigData($gradeLevel->type);

                $config = AssessmentConfig::updateOrCreate(
                    [
                        'school_id' => $academicYear->school_id,
                        'academic_year_id' => $academicYear->id,
                        'grade_level_id' => $gradeLevelId,
                    ],
                    $configData,
                );

                $this->seedInstruments($config);

                if (in_array($gradeLevel->type, [GradeLevelType::ElementaryEarly, GradeLevelType::ElementaryLate], true)) {
                    $this->seedConceptualScales($config);
                }
            }
        }
    }

    private function resolveConfigData(GradeLevelType $type): array
    {
        if ($type === GradeLevelType::EarlyChildhood) {
            return [
                'grade_type' => 'descriptive',
                'scale_min' => 0,
                'scale_max' => 10,
                'passing_grade' => 6.00,
                'average_formula' => 'arithmetic',
                'rounding_precision' => 1,
                'recovery_enabled' => false,
                'recovery_replaces' => 'higher',
            ];
        }

        return [
            'grade_type' => 'numeric',
            'scale_min' => 0,
            'scale_max' => 10,
            'passing_grade' => 6.00,
            'average_formula' => 'arithmetic',
            'rounding_precision' => 1,
            'recovery_enabled' => true,
            'recovery_replaces' => 'higher',
        ];
    }

    private function seedInstruments(AssessmentConfig $config): void
    {
        foreach (self::INSTRUMENTS as $instrument) {
            AssessmentInstrument::updateOrCreate(
                [
                    'assessment_config_id' => $config->id,
                    'name' => $instrument['name'],
                ],
                [
                    'weight' => $instrument['weight'],
                    'max_value' => $instrument['max_value'],
                    'order' => $instrument['order'],
                ],
            );
        }
    }

    private function seedConceptualScales(AssessmentConfig $config): void
    {
        foreach (self::CONCEPTUAL_SCALES as $scale) {
            ConceptualScale::updateOrCreate(
                [
                    'assessment_config_id' => $config->id,
                    'code' => $scale['code'],
                ],
                [
                    'label' => $scale['label'],
                    'numeric_equivalent' => $scale['numeric_equivalent'],
                    'passing' => $scale['passing'],
                    'order' => $scale['order'],
                ],
            );
        }
    }
}
