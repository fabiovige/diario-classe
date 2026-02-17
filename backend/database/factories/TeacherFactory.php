<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Teacher> */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'school_id' => School::factory(),
            'registration_number' => (string) fake()->unique()->numerify('######'),
            'specialization' => fake()->randomElement([
                'Pedagogia',
                'Matemática',
                'Português',
                'Ciências',
                'História',
                'Geografia',
                'Educação Física',
                'Artes',
                'Inglês',
            ]),
            'hire_date' => fake()->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
            'active' => true,
        ];
    }
}
