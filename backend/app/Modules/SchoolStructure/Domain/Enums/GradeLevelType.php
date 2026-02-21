<?php

namespace App\Modules\SchoolStructure\Domain\Enums;

enum GradeLevelType: string
{
    case EarlyChildhood = 'early_childhood';
    case Elementary = 'elementary';
    case HighSchool = 'high_school';

    public function label(): string
    {
        return match ($this) {
            self::EarlyChildhood => 'Ed. Infantil',
            self::Elementary => 'Fundamental',
            self::HighSchool => 'Ensino Médio',
        };
    }
}
