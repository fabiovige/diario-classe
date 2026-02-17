<?php

namespace Database\Factories;

use App\Modules\People\Domain\Entities\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Guardian> */
class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'name' => fake('pt_BR')->name(),
            'cpf' => fake('pt_BR')->unique()->cpf(false),
            'phone' => fake('pt_BR')->cellphoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake('pt_BR')->address(),
            'occupation' => fake('pt_BR')->jobTitle(),
        ];
    }
}
