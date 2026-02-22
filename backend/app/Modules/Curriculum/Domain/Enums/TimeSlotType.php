<?php

namespace App\Modules\Curriculum\Domain\Enums;

enum TimeSlotType: string
{
    case Class_ = 'class';
    case Break = 'break';

    public function label(): string
    {
        return match ($this) {
            self::Class_ => 'Aula',
            self::Break => 'Intervalo',
        };
    }
}
