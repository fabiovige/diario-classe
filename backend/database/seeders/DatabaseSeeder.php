<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            GradeLevelSeeder::class,
            SchoolSeeder::class,
            ShiftSeeder::class,
            AcademicYearSeeder::class,
            UserSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            GuardianSeeder::class,
            ClassGroupSeeder::class,
            EnrollmentSeeder::class,
            AssessmentPeriodSeeder::class,
            CurricularComponentSeeder::class,
            ExperienceFieldSeeder::class,
            TeacherAssignmentSeeder::class,
            TimeSlotSeeder::class,
            AttendanceConfigSeeder::class,
            AssessmentConfigSeeder::class,
            LessonRecordSeeder::class,
            AttendanceRecordSeeder::class,
            AbsenceJustificationSeeder::class,
            GradeSeeder::class,
            DescriptiveReportSeeder::class,
            PeriodAverageSeeder::class,
            PeriodClosingSeeder::class,
            FinalResultSeeder::class,
        ]);
    }
}
