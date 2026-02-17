<?php

namespace App\Modules\PeriodClosing\Domain\Enums;

enum FinalResult: string
{
    case Approved = 'approved';
    case Retained = 'retained';
    case PartialProgression = 'partial_progression';
    case Transferred = 'transferred';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Aprovado',
            self::Retained => 'Retido',
            self::PartialProgression => 'Progressão Parcial',
            self::Transferred => 'Transferido',
            self::Abandoned => 'Abandonou',
        };
    }
}
