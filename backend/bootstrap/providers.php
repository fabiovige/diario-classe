<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Shared\Audit\Infrastructure\Providers\AuditServiceProvider::class,
    App\Modules\Identity\Infrastructure\Providers\IdentityServiceProvider::class,
    App\Modules\SchoolStructure\Infrastructure\Providers\SchoolStructureServiceProvider::class,
    App\Modules\People\Infrastructure\Providers\PeopleServiceProvider::class,
    App\Modules\Enrollment\Infrastructure\Providers\EnrollmentServiceProvider::class,
    App\Modules\AcademicCalendar\Infrastructure\Providers\AcademicCalendarServiceProvider::class,
    App\Modules\Curriculum\Infrastructure\Providers\CurriculumServiceProvider::class,
    App\Modules\Attendance\Infrastructure\Providers\AttendanceServiceProvider::class,
    App\Modules\ClassRecord\Infrastructure\Providers\ClassRecordServiceProvider::class,
    App\Modules\Assessment\Infrastructure\Providers\AssessmentServiceProvider::class,
    App\Modules\PeriodClosing\Infrastructure\Providers\PeriodClosingServiceProvider::class,
];
