<?php

namespace Database\Seeders;

use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $gradeLevels = [
            ['name' => 'Berçário I', 'type' => 'early_childhood', 'order' => 1, 'min_age_months' => 4],
            ['name' => 'Berçário II', 'type' => 'early_childhood', 'order' => 2, 'min_age_months' => 16],
            ['name' => 'Maternal I', 'type' => 'early_childhood', 'order' => 3, 'min_age_months' => 24],
            ['name' => 'Maternal II', 'type' => 'early_childhood', 'order' => 4, 'min_age_months' => 36],
            ['name' => 'Pré I', 'type' => 'early_childhood', 'order' => 5, 'min_age_months' => 48],
            ['name' => 'Pré II', 'type' => 'early_childhood', 'order' => 6, 'min_age_months' => 60],
            ['name' => '1º Ano', 'type' => 'elementary_early', 'order' => 7, 'min_age_months' => 72],
            ['name' => '2º Ano', 'type' => 'elementary_early', 'order' => 8, 'min_age_months' => 84],
            ['name' => '3º Ano', 'type' => 'elementary_early', 'order' => 9, 'min_age_months' => 96],
            ['name' => '4º Ano', 'type' => 'elementary_early', 'order' => 10, 'min_age_months' => 108],
            ['name' => '5º Ano', 'type' => 'elementary_early', 'order' => 11, 'min_age_months' => 120],
            ['name' => '6º Ano', 'type' => 'elementary_late', 'order' => 12, 'min_age_months' => 132],
            ['name' => '7º Ano', 'type' => 'elementary_late', 'order' => 13, 'min_age_months' => 144],
            ['name' => '8º Ano', 'type' => 'elementary_late', 'order' => 14, 'min_age_months' => 156],
            ['name' => '9º Ano', 'type' => 'elementary_late', 'order' => 15, 'min_age_months' => 168],
            ['name' => '1º Ano EM', 'type' => 'high_school', 'order' => 16, 'min_age_months' => 180],
            ['name' => '2º Ano EM', 'type' => 'high_school', 'order' => 17, 'min_age_months' => 192],
            ['name' => '3º Ano EM', 'type' => 'high_school', 'order' => 18, 'min_age_months' => 204],
        ];

        foreach ($gradeLevels as $gradeLevel) {
            GradeLevel::updateOrCreate(
                ['name' => $gradeLevel['name']],
                $gradeLevel,
            );
        }
    }
}
