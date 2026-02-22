<?php

namespace Database\Seeders;

use App\Modules\People\Domain\Enums\DisabilityType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    private const TOTAL_STUDENTS = 2000;

    private const RACE_COLOR_DISTRIBUTION = [
        'parda' => 43,
        'branca' => 43,
        'preta' => 9,
        'amarela' => 2,
        'indigena' => 1,
        'nao_declarada' => 2,
    ];

    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');
        $racePool = $this->buildRacePool();
        $disabilityTypes = DisabilityType::cases();
        $disabilityCount = count($disabilityTypes);
        $now = now()->toDateTimeString();

        $batch = [];

        for ($i = 0; $i < self::TOTAL_STUDENTS; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            $firstName = $gender === 'male' ? $faker->firstNameMale() : $faker->firstNameFemale();
            $name = "{$firstName} {$faker->lastName()} {$faker->lastName()}";
            $hasDisability = $faker->boolean(5);
            $hasSocialName = $faker->boolean(10);

            $batch[] = [
                'name' => $name,
                'social_name' => $hasSocialName ? $faker->firstName() . ' ' . $faker->lastName() : null,
                'birth_date' => $faker->dateTimeBetween('-14 years', '-4 years')->format('Y-m-d'),
                'gender' => $gender,
                'race_color' => $racePool[array_rand($racePool)],
                'cpf' => $faker->unique()->cpf(false),
                'sus_number' => $faker->numerify('###############'),
                'birth_city' => 'Jandira',
                'birth_state' => 'SP',
                'nationality' => 'brasileira',
                'has_disability' => $hasDisability,
                'disability_type' => $hasDisability ? $disabilityTypes[$i % $disabilityCount]->value : null,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 500) {
                DB::table('students')->insert($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('students')->insert($batch);
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
