<?php

namespace Database\Factories;

use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\AssessmentInstrument;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AssessmentInstrument> */
class AssessmentInstrumentFactory extends Factory
{
    protected $model = AssessmentInstrument::class;

    public function definition(): array
    {
        return [
            'assessment_config_id' => AssessmentConfig::factory(),
            'name' => fake()->randomElement(['Prova', 'Trabalho', 'Participação', 'Seminário']),
            'weight' => 1.00,
            'max_value' => 10.00,
            'order' => 1,
        ];
    }
}
