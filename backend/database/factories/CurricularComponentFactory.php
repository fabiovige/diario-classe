<?php

namespace Database\Factories;

use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Enums\KnowledgeArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CurricularComponent> */
class CurricularComponentFactory extends Factory
{
    protected $model = CurricularComponent::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->word(),
            'knowledge_area' => fake()->randomElement(array_column(KnowledgeArea::cases(), 'value')),
            'code' => strtoupper(fake()->lexify('???')),
            'active' => true,
        ];
    }
}
