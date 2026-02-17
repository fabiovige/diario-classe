<?php

namespace Database\Seeders;

use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use Illuminate\Database\Seeder;

class ExperienceFieldSeeder extends Seeder
{
    private const FIELDS = [
        ['name' => 'O eu, o outro e o nós', 'code' => 'EF01'],
        ['name' => 'Corpo, gestos e movimentos', 'code' => 'EF02'],
        ['name' => 'Traços, sons, cores e formas', 'code' => 'EF03'],
        ['name' => 'Escuta, fala, pensamento e imaginação', 'code' => 'EF04'],
        ['name' => 'Espaços, tempos, quantidades, relações e transformações', 'code' => 'EF05'],
    ];

    public function run(): void
    {
        foreach (self::FIELDS as $field) {
            ExperienceField::updateOrCreate(
                ['name' => $field['name']],
                [
                    'code' => $field['code'],
                    'active' => true,
                ],
            );
        }
    }
}
