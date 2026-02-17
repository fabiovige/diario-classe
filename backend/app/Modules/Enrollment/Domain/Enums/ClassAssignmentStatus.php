<?php

namespace App\Modules\Enrollment\Domain\Enums;

enum ClassAssignmentStatus: string
{
    case Active = 'active';
    case Transferred = 'transferred';
    case Cancelled = 'cancelled';
}
