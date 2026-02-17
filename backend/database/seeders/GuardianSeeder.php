<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\People\Domain\Entities\Guardian;
use App\Modules\People\Domain\Entities\Student;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GuardianSeeder extends Seeder
{
    private const SECONDARY_RELATIONSHIPS = ['avo', 'avoa', 'tio', 'tia', 'irmao', 'responsavel_legal'];

    public function run(): void
    {
        $faker = FakerFactory::create('pt_BR');
        $guardianRole = Role::where('slug', 'guardian')->firstOrFail();
        $students = Student::all();

        foreach ($students as $student) {
            $primaryRelationship = $faker->randomElement(['mae', 'pai']);
            $primaryGuardian = $this->createGuardian($faker, $guardianRole, $primaryRelationship);

            $student->guardians()->attach($primaryGuardian->id, [
                'relationship' => $primaryRelationship,
                'is_primary' => true,
                'can_pickup' => true,
            ]);

            if (! $faker->boolean(60)) {
                continue;
            }

            $secondaryRelationship = $primaryRelationship === 'mae' ? 'pai' : 'mae';
            if ($faker->boolean(20)) {
                $secondaryRelationship = $faker->randomElement(self::SECONDARY_RELATIONSHIPS);
            }

            $secondaryGuardian = $this->createGuardian($faker, $guardianRole, $secondaryRelationship);

            $student->guardians()->attach($secondaryGuardian->id, [
                'relationship' => $secondaryRelationship,
                'is_primary' => false,
                'can_pickup' => $faker->boolean(80),
            ]);
        }
    }

    private function createGuardian(
        \Faker\Generator $faker,
        Role $guardianRole,
        string $relationship,
    ): Guardian {
        $isFemale = in_array($relationship, ['mae', 'avoa', 'tia'], true);
        $name = $isFemale ? $faker->name('female') : $faker->name('male');
        $email = Str::slug($name, '.').'.'.$faker->unique()->numerify('###').'@email.com';

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'status' => 'active',
            'role_id' => $guardianRole->id,
            'cpf' => $faker->unique()->cpf(false),
        ]);

        return Guardian::create([
            'name' => $name,
            'cpf' => $user->cpf,
            'phone' => $faker->phoneNumber(),
            'phone_secondary' => $faker->boolean(40) ? $faker->phoneNumber() : null,
            'email' => $email,
            'address' => $faker->streetAddress().', '.$faker->city().'-SP',
            'occupation' => $faker->jobTitle(),
            'user_id' => $user->id,
        ]);
    }
}
