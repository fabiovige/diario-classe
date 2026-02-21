<?php

namespace App\Modules\SchoolStructure\Domain\Enums;

enum ShiftName: string
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Evening = 'evening';
    case FullTime = 'full_time';

    public function label(): string
    {
        return match ($this) {
            self::Morning => 'Manhã',
            self::Afternoon => 'Tarde',
            self::Evening => 'Noite',
            self::FullTime => 'Integral',
        };
    }
}
