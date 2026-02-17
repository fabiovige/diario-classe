<?php

namespace Database\Seeders;

use App\Modules\People\Domain\Entities\Student;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    private const TOTAL_STUDENTS = 500;

    private const RACE_COLOR_DISTRIBUTION = [
        'parda' => 43,
        'branca' => 43,
        'preta' => 9,
        'amarela' => 2,
        'indigena' => 1,
        'nao_declarada' => 2,
    ];

    private const DISABILITY_TYPES = [
        'Visual',
        'Auditiva',
        'Física',
        'Intelectual',
        'TEA',
        'Altas Habilidades',
    ];

    public function run(): void
    {
        $faker = FakerFactory::create('pt_BR');
        $racePool = $this->buildRacePool();

        for ($i = 0; $i < self::TOTAL_STUDENTS; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            $firstName = $gender === 'male' ? $faker->firstNameMale() : $faker->firstNameFemale();
            $name = "{$firstName} {$faker->lastName()} {$faker->lastName()}";
            $hasDisability = $faker->boolean(5);

            Student::create([
                'name' => $name,
                'birth_date' => $faker->dateTimeBetween('-14 years', '-4 years')->format('Y-m-d'),
                'gender' => $gender,
                'race_color' => $racePool[array_rand($racePool)],
                'cpf' => $faker->unique()->cpf(false),
                'sus_number' => $faker->numerify('###############'),
                'birth_city' => 'Jandira',
                'birth_state' => 'SP',
                'nationality' => 'brasileira',
                'has_disability' => $hasDisability,
                'disability_type' => $hasDisability ? $faker->randomElement(self::DISABILITY_TYPES) : null,
                'active' => true,
            ]);
        }
    }

    /** @return array<int, string> */
    private function buildRacePool(): array
    {
        $pool = [];
        foreach (self::RACE_COLOR_DISTRIBUTION as $race => $weight) {
            $pool = array_merge($pool, array_fill(0, $weight, $race));
        }

        return $pool;
    }
}
