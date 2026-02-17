<?php

namespace App\Modules\Enrollment\Domain\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Transferred = 'transferred';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case Abandoned = 'abandoned';
}
