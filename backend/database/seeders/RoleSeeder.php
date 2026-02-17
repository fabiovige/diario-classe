<?php

namespace Database\Seeders;

use App\Modules\Identity\Domain\Entities\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'permissions' => ['*'],
            ],
            [
                'name' => 'Diretor(a)',
                'slug' => 'director',
                'permissions' => [
                    'schools.view', 'schools.manage',
                    'users.view', 'users.manage',
                    'students.view', 'students.manage',
                    'teachers.view', 'teachers.manage',
                    'enrollments.view', 'enrollments.manage',
                    'class_groups.view', 'class_groups.manage',
                    'attendance.view', 'attendance.manage',
                    'grades.view', 'grades.manage',
                    'reports.view', 'reports.generate',
                ],
            ],
            [
                'name' => 'Secretário(a)',
                'slug' => 'secretary',
                'permissions' => [
                    'students.view', 'students.manage',
                    'guardians.view', 'guardians.manage',
                    'enrollments.view', 'enrollments.manage',
                    'class_groups.view', 'class_groups.manage',
                    'reports.view',
                ],
            ],
            [
                'name' => 'Coordenador(a)',
                'slug' => 'coordinator',
                'permissions' => [
                    'students.view', 'students.manage',
                    'teachers.view',
                    'attendance.view', 'attendance.manage',
                    'grades.view', 'grades.manage',
                    'class_record.view', 'class_record.manage',
                    'reports.view',
                ],
            ],
            [
                'name' => 'Professor(a)',
                'slug' => 'teacher',
                'permissions' => [
                    'students.view',
                    'attendance.manage',
                    'grades.manage',
                    'class_record.manage',
                ],
            ],
            [
                'name' => 'Responsável',
                'slug' => 'guardian',
                'permissions' => [
                    'students.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData,
            );
        }
    }
}
