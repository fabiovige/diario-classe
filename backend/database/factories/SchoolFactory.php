<?php

namespace Database\Factories;

use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\SchoolStructure\Domain\Enums\SchoolType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<School> */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'name' => 'EMEB '.fake('pt_BR')->name(),
            'inep_code' => (string) fake()->unique()->numerify('########'),
            'type' => SchoolType::Municipal->value,
            'address' => fake('pt_BR')->address(),
            'phone' => fake('pt_BR')->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'active' => true,
        ];
    }
}
