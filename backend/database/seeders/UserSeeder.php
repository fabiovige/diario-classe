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

        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $directorRole = Role::where('slug', 'director')->firstOrFail();
        $secretaryRole = Role::where('slug', 'secretary')->firstOrFail();
        $coordinatorRole = Role::where('slug', 'coordinator')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'admin@jandira.sp.gov.br'],
            [
                'name' => 'Administrador do Sistema',
                'password' => 'admin123',
                'status' => 'active',
                'role_id' => $adminRole->id,
                'cpf' => $this->generateCpf($faker),
            ],
        );

        $schools = School::orderBy('id')->get();

        foreach ($schools as $school) {
            $this->createSchoolUser($faker, $school, $directorRole);
            $this->createSchoolUser($faker, $school, $secretaryRole);
            $this->createSchoolUser($faker, $school, $coordinatorRole);
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
