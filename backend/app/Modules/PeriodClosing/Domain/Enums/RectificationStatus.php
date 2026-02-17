<?php

namespace App\Modules\PeriodClosing\Domain\Enums;

enum RectificationStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Solicitada',
            self::Approved => 'Aprovada',
            self::Rejected => 'Rejeitada',
        };
    }
}
