<?php

namespace Database\Seeders;

use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use Illuminate\Database\Seeder;

class CurricularComponentSeeder extends Seeder
{
    private const COMPONENTS = [
        ['name' => 'Língua Portuguesa', 'knowledge_area' => 'linguagens', 'code' => 'LP'],
        ['name' => 'Matemática', 'knowledge_area' => 'matematica', 'code' => 'MAT'],
        ['name' => 'Ciências', 'knowledge_area' => 'ciencias_natureza', 'code' => 'CIE'],
        ['name' => 'História', 'knowledge_area' => 'ciencias_humanas', 'code' => 'HIS'],
        ['name' => 'Geografia', 'knowledge_area' => 'ciencias_humanas', 'code' => 'GEO'],
        ['name' => 'Arte', 'knowledge_area' => 'linguagens', 'code' => 'ART'],
        ['name' => 'Educação Física', 'knowledge_area' => 'linguagens', 'code' => 'EDF'],
        ['name' => 'Língua Inglesa', 'knowledge_area' => 'linguagens', 'code' => 'ING'],
        ['name' => 'Ensino Religioso', 'knowledge_area' => 'ensino_religioso', 'code' => 'ER'],
        ['name' => 'Informática', 'knowledge_area' => 'parte_diversificada', 'code' => 'INF'],
    ];

    private const INACTIVE_CODES = ['INF'];

    public function run(): void
    {
        foreach (self::COMPONENTS as $component) {
            CurricularComponent::updateOrCreate(
                ['name' => $component['name']],
                [
                    'knowledge_area' => $component['knowledge_area'],
                    'code' => $component['code'],
                    'active' => ! in_array($component['code'], self::INACTIVE_CODES),
                ],
            );
        }
    }
}
