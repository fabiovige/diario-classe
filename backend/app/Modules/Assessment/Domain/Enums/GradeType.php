<?php

namespace App\Modules\Assessment\Domain\Enums;

enum GradeType: string
{
    case Numeric = 'numeric';
    case Conceptual = 'conceptual';
    case Descriptive = 'descriptive';

    public function label(): string
    {
        return match ($this) {
            self::Numeric => 'Numérica',
            self::Conceptual => 'Conceitual',
            self::Descriptive => 'Descritiva',
        };
    }
}
