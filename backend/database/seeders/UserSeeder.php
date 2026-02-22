<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');
        $roles = DB::table('roles')->pluck('id', 'slug');
        $schools = DB::table('schools')->orderBy('id')->pluck('id');
        $passwordHash = Hash::make('password');
        $adminHash = Hash::make('admin123');
        $now = now()->toDateTimeString();

        DB::table('users')->insert([
            'name' => 'Administrador do Sistema',
            'email' => 'admin@jandira.sp.gov.br',
            'password' => $adminHash,
            'status' => 'active',
            'role_id' => $roles['admin'],
            'cpf' => $faker->unique()->cpf(false),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $schoolRoleSlugs = ['director', 'secretary', 'coordinator', 'teacher', 'guardian'];
        $batch = [];

        foreach ($schools as $schoolId) {
            foreach ($schoolRoleSlugs as $slug) {
                $name = $faker->name();
                $batch[] = [
                    'name' => $name,
                    'email' => Str::slug($name, '.') . '.' . count($batch) . '@jandira.sp.gov.br',
                    'password' => $passwordHash,
                    'status' => 'active',
                    'role_id' => $roles[$slug],
                    'school_id' => $schoolId,
                    'cpf' => $faker->unique()->cpf(false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }
    }
}
