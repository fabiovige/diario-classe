<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    private const TOTAL_TEACHERS = 100;

    private const SPECIALIZATIONS = [
        'Pedagogia',
        'Matemática',
        'Português',
        'Ciências',
        'História',
        'Geografia',
        'Ed. Física',
        'Artes',
        'Inglês',
    ];

    public function run(): void
    {
        $faker = FakerFactory::create('pt_BR');
        $teacherRole = Role::where('slug', 'teacher')->firstOrFail();
        $schools = School::orderBy('id')->get();
        $schoolCount = $schools->count();

        for ($i = 0; $i < self::TOTAL_TEACHERS; $i++) {
            $school = $schools[$i % $schoolCount];
            $name = $faker->name();
            $email = Str::slug($name, '.').'@jandira.sp.gov.br';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password',
                    'status' => 'active',
                    'role_id' => $teacherRole->id,
                    'school_id' => $school->id,
                    'cpf' => $faker->unique()->cpf(false),
                ],
            );

            Teacher::updateOrCreate(
                ['user_id' => $user->id, 'school_id' => $school->id],
                [
                    'registration_number' => sprintf('PROF%05d', $i + 1),
                    'specialization' => self::SPECIALIZATIONS[array_rand(self::SPECIALIZATIONS)],
                    'hire_date' => $faker->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
                    'active' => true,
                ],
            );
        }
    }
}
