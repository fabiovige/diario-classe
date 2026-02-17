<?php

namespace Database\Factories;

use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExperienceField> */
class ExperienceFieldFactory extends Factory
{
    protected $model = ExperienceField::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentence(3),
            'code' => strtoupper(fake()->lexify('??')),
            'active' => true,
        ];
    }
}
