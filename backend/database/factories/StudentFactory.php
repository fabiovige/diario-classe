<?php

namespace Database\Factories;

use App\Modules\People\Domain\Entities\Student;
use App\Modules\People\Domain\Enums\Gender;
use App\Modules\People\Domain\Enums\RaceColor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Student> */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());

        return [
            'name' => fake('pt_BR')->name($gender === Gender::Male ? 'male' : 'female'),
            'birth_date' => fake()->dateTimeBetween('-14 years', '-4 years')->format('Y-m-d'),
            'gender' => $gender->value,
            'race_color' => fake()->randomElement(RaceColor::cases())->value,
            'cpf' => fake('pt_BR')->unique()->cpf(false),
            'birth_city' => 'Jandira',
            'birth_state' => 'SP',
            'nationality' => 'brasileira',
            'has_disability' => fake()->boolean(5),
            'active' => true,
        ];
    }
}
