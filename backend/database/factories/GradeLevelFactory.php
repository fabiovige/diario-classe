<?php

namespace Database\Factories;

use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Domain\Enums\GradeLevelType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GradeLevel> */
class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['1º Ano', '2º Ano', '3º Ano', '4º Ano', '5º Ano']),
            'type' => GradeLevelType::Elementary->value,
            'order' => fake()->unique()->numberBetween(1, 15),
        ];
    }
}
