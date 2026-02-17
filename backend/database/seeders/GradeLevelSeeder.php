<?php

namespace Database\Seeders;

use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $gradeLevels = [
            ['name' => 'Berçário I', 'type' => 'early_childhood', 'order' => 1],
            ['name' => 'Berçário II', 'type' => 'early_childhood', 'order' => 2],
            ['name' => 'Maternal I', 'type' => 'early_childhood', 'order' => 3],
            ['name' => 'Maternal II', 'type' => 'early_childhood', 'order' => 4],
            ['name' => 'Pré I', 'type' => 'early_childhood', 'order' => 5],
            ['name' => 'Pré II', 'type' => 'early_childhood', 'order' => 6],
            ['name' => '1º Ano', 'type' => 'elementary', 'order' => 7],
            ['name' => '2º Ano', 'type' => 'elementary', 'order' => 8],
            ['name' => '3º Ano', 'type' => 'elementary', 'order' => 9],
            ['name' => '4º Ano', 'type' => 'elementary', 'order' => 10],
            ['name' => '5º Ano', 'type' => 'elementary', 'order' => 11],
            ['name' => '6º Ano', 'type' => 'elementary', 'order' => 12],
            ['name' => '7º Ano', 'type' => 'elementary', 'order' => 13],
            ['name' => '8º Ano', 'type' => 'elementary', 'order' => 14],
            ['name' => '9º Ano', 'type' => 'elementary', 'order' => 15],
            ['name' => '1º Ano EM', 'type' => 'high_school', 'order' => 16],
            ['name' => '2º Ano EM', 'type' => 'high_school', 'order' => 17],
            ['name' => '3º Ano EM', 'type' => 'high_school', 'order' => 18],
        ];

        foreach ($gradeLevels as $gradeLevel) {
            GradeLevel::updateOrCreate(
                ['name' => $gradeLevel['name']],
                $gradeLevel,
            );
        }
    }
}
