<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake('pt_BR')->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'status' => UserStatus::Active->value,
            'remember_token' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => UserStatus::Inactive->value]);
    }

    public function blocked(): static
    {
        return $this->state(['status' => UserStatus::Blocked->value]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
