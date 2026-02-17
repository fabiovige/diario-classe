<?php

namespace Database\Factories;

use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Role> */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Administrador', 'Diretor', 'Secretário', 'Coordenador', 'Professor', 'Responsável']),
            'slug' => fake()->unique()->randomElement(RoleSlug::cases())->value,
            'permissions' => [],
        ];
    }

    public function admin(): static
    {
        return $this->state([
            'name' => 'Administrador',
            'slug' => RoleSlug::Admin->value,
            'permissions' => ['*'],
        ]);
    }

    public function teacher(): static
    {
        return $this->state([
            'name' => 'Professor(a)',
            'slug' => RoleSlug::Teacher->value,
            'permissions' => ['students.view', 'attendance.manage', 'grades.manage'],
        ]);
    }
}
