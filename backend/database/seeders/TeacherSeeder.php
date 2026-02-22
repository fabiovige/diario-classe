<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    private const TOTAL_TEACHERS = 300;

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
        $faker = \Faker\Factory::create('pt_BR');
        $teacherRoleId = DB::table('roles')->where('slug', 'teacher')->value('id');
        $schools = DB::table('schools')->orderBy('id')->pluck('id');
        $schoolCount = $schools->count();
        $passwordHash = Hash::make('password');
        $now = now()->toDateTimeString();
        $specCount = count(self::SPECIALIZATIONS);

        $userBatch = [];
        $teacherData = [];

        for ($i = 0; $i < self::TOTAL_TEACHERS; $i++) {
            $schoolId = $schools[$i % $schoolCount];
            $name = $faker->name();
            $email = Str::slug($name, '.') . '.' . ($i + 1) . '@jandira.sp.gov.br';
            $cpf = $faker->unique()->cpf(false);

            $userBatch[] = [
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
                'status' => 'active',
                'role_id' => $teacherRoleId,
                'school_id' => $schoolId,
                'cpf' => $cpf,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $teacherData[] = [
                'email' => $email,
                'school_id' => $schoolId,
                'registration_number' => sprintf('PROF%05d', $i + 1),
                'specialization' => self::SPECIALIZATIONS[$i % $specCount],
                'hire_date' => $faker->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
            ];
        }

        foreach (array_chunk($userBatch, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $userIds = DB::table('users')
            ->where('role_id', $teacherRoleId)
            ->pluck('id', 'email');

        $teacherBatch = [];
        foreach ($teacherData as $data) {
            $teacherBatch[] = [
                'user_id' => $userIds[$data['email']],
                'school_id' => $data['school_id'],
                'registration_number' => $data['registration_number'],
                'specialization' => $data['specialization'],
                'hire_date' => $data['hire_date'],
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($teacherBatch, 500) as $chunk) {
            DB::table('teachers')->insert($chunk);
        }
    }
}
