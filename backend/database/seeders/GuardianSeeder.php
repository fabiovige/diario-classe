<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuardianSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

    private const SECONDARY_RELATIONSHIPS = ['avo', 'avoa', 'tio', 'tia', 'irmao', 'responsavel_legal'];

    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');
        $guardianRoleId = DB::table('roles')->where('slug', 'guardian')->value('id');
        $studentIds = DB::table('students')->pluck('id')->toArray();
        $passwordHash = Hash::make('password');
        $now = now()->toDateTimeString();

        $userBatch = [];
        $guardianMeta = [];
        $emailCounter = 0;

        foreach ($studentIds as $studentId) {
            $primaryRelationship = $faker->randomElement(['mae', 'pai']);
            $isFemale = $primaryRelationship === 'mae';
            $name = $isFemale ? $faker->name('female') : $faker->name('male');
            $email = Str::slug($name, '.') . '.' . (++$emailCounter) . '@email.com';

            $userBatch[] = [
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
                'status' => 'active',
                'role_id' => $guardianRoleId,
                'cpf' => $faker->unique()->cpf(false),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $guardianMeta[] = [
                'email' => $email,
                'name' => $name,
                'student_id' => $studentId,
                'relationship' => $primaryRelationship,
                'is_primary' => true,
                'phone' => $faker->phoneNumber(),
                'phone_secondary' => $faker->boolean(40) ? $faker->phoneNumber() : null,
                'address' => $faker->streetAddress() . ', ' . $faker->city() . '-SP',
                'occupation' => $faker->jobTitle(),
            ];

            if ($faker->boolean(60)) {
                $secondaryRelationship = $primaryRelationship === 'mae' ? 'pai' : 'mae';
                if ($faker->boolean(20)) {
                    $secondaryRelationship = $faker->randomElement(self::SECONDARY_RELATIONSHIPS);
                }

                $secFemale = in_array($secondaryRelationship, ['mae', 'avoa', 'tia'], true);
                $secName = $secFemale ? $faker->name('female') : $faker->name('male');
                $secEmail = Str::slug($secName, '.') . '.' . (++$emailCounter) . '@email.com';

                $userBatch[] = [
                    'name' => $secName,
                    'email' => $secEmail,
                    'password' => $passwordHash,
                    'status' => 'active',
                    'role_id' => $guardianRoleId,
                    'cpf' => $faker->unique()->cpf(false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $guardianMeta[] = [
                    'email' => $secEmail,
                    'name' => $secName,
                    'student_id' => $studentId,
                    'relationship' => $secondaryRelationship,
                    'is_primary' => false,
                    'phone' => $faker->phoneNumber(),
                    'phone_secondary' => $faker->boolean(40) ? $faker->phoneNumber() : null,
                    'address' => $faker->streetAddress() . ', ' . $faker->city() . '-SP',
                    'occupation' => $faker->jobTitle(),
                ];
            }
        }

        foreach (array_chunk($userBatch, self::BATCH_SIZE) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $this->command->info('  Guardians: users criados, montando guardians...');

        $userIds = DB::table('users')
            ->where('role_id', $guardianRoleId)
            ->pluck('id', 'email');

        $cpfByEmail = [];
        foreach ($userBatch as $user) {
            $cpfByEmail[$user['email']] = $user['cpf'];
        }

        $guardianBatch = [];
        $pivotBatch = [];
        $guardianEmails = [];

        foreach ($guardianMeta as $meta) {
            $userId = $userIds[$meta['email']];

            $guardianBatch[] = [
                'name' => $meta['name'],
                'cpf' => $cpfByEmail[$meta['email']],
                'phone' => $meta['phone'],
                'phone_secondary' => $meta['phone_secondary'],
                'email' => $meta['email'],
                'address' => $meta['address'],
                'occupation' => $meta['occupation'],
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $guardianEmails[] = [
                'email' => $meta['email'],
                'student_id' => $meta['student_id'],
                'relationship' => $meta['relationship'],
                'is_primary' => $meta['is_primary'],
            ];
        }

        foreach (array_chunk($guardianBatch, self::BATCH_SIZE) as $chunk) {
            DB::table('guardians')->insert($chunk);
        }

        $this->command->info('  Guardians: guardians criados, montando vinculos...');

        $guardianIds = DB::table('guardians')->pluck('id', 'email');

        foreach ($guardianEmails as $link) {
            $pivotBatch[] = [
                'student_id' => $link['student_id'],
                'guardian_id' => $guardianIds[$link['email']],
                'relationship' => $link['relationship'],
                'is_primary' => $link['is_primary'],
                'can_pickup' => $link['is_primary'] || rand(1, 100) <= 80,
            ];
        }

        foreach (array_chunk($pivotBatch, self::BATCH_SIZE) as $chunk) {
            DB::table('student_guardian')->insert($chunk);
        }

        $this->command->info('  Guardians: ' . count($guardianMeta) . ' responsaveis finalizados.');
    }
}
