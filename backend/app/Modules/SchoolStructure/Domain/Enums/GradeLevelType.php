<?php

namespace App\Modules\SchoolStructure\Domain\Enums;

enum GradeLevelType: string
{
    case EarlyChildhood = 'early_childhood';
    case Elementary = 'elementary';
    case HighSchool = 'high_school';
}
