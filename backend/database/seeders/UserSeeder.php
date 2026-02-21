<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('pt_BR');

        $roles = Role::all()->keyBy(fn (Role $r) => $r->slug->value);

        User::updateOrCreate(
            ['email' => 'admin@jandira.sp.gov.br'],
            [
                'name' => 'Administrador do Sistema',
                'password' => 'admin123',
                'status' => 'active',
                'role_id' => $roles['admin']->id,
                'cpf' => $this->generateCpf($faker),
            ],
        );

        $schoolRoleSlugs = ['director', 'secretary', 'coordinator', 'teacher', 'guardian'];
        $schools = School::orderBy('id')->get();

        foreach ($schools as $school) {
            foreach ($schoolRoleSlugs as $slug) {
                $this->createSchoolUser($faker, $school, $roles[$slug]);
            }
        }
    }

    private function createSchoolUser(
        \Faker\Generator $faker,
        School $school,
        Role $role,
    ): User {
        $name = $faker->name();
        $email = $this->generateEmail($name);

        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'status' => 'active',
                'role_id' => $role->id,
                'school_id' => $school->id,
                'cpf' => $this->generateCpf($faker),
            ],
        );
    }

    private function generateEmail(string $name): string
    {
        $slug = Str::slug($name, '.');

        return "{$slug}@jandira.sp.gov.br";
    }

    private function generateCpf(\Faker\Generator $faker): string
    {
        return $faker->unique()->cpf(false);
    }
}
