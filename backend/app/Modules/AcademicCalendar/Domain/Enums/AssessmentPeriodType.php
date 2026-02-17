<?php

namespace App\Modules\AcademicCalendar\Domain\Enums;

enum AssessmentPeriodType: string
{
    case Bimestral = 'bimestral';
    case Trimestral = 'trimestral';
    case Semestral = 'semestral';

    public function label(): string
    {
        return match ($this) {
            self::Bimestral => 'Bimestral',
            self::Trimestral => 'Trimestral',
            self::Semestral => 'Semestral',
        };
    }

    public function maxPeriods(): int
    {
        return match ($this) {
            self::Bimestral => 4,
            self::Trimestral => 3,
            self::Semestral => 2,
        };
    }
}
