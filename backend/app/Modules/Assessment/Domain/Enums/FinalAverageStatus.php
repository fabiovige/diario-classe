<?php

namespace App\Modules\Assessment\Domain\Enums;

enum FinalAverageStatus: string
{
    case Pending = 'pending';
    case Calculated = 'calculated';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Calculated => 'Calculada',
            self::Approved => 'Aprovada',
        };
    }
}
