<?php

namespace App\Modules\AcademicCalendar\Domain\Enums;

enum AssessmentPeriodStatus: string
{
    case Open = 'open';
    case Closing = 'closing';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Aberto',
            self::Closing => 'Em Fechamento',
            self::Closed => 'Fechado',
        };
    }
}
