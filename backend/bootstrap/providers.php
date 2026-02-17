<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Shared\Audit\Infrastructure\Providers\AuditServiceProvider::class,
    App\Modules\Identity\Infrastructure\Providers\IdentityServiceProvider::class,
    App\Modules\SchoolStructure\Infrastructure\Providers\SchoolStructureServiceProvider::class,
    App\Modules\People\Infrastructure\Providers\PeopleServiceProvider::class,
    App\Modules\Enrollment\Infrastructure\Providers\EnrollmentServiceProvider::class,
];
