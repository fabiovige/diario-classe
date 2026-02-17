<?php

namespace App\Modules\SchoolStructure\Domain\Enums;

enum AcademicYearStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case Closing = 'closing';
    case Closed = 'closed';
}
