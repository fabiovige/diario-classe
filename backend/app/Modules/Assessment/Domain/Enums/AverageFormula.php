<?php

namespace App\Modules\Assessment\Domain\Enums;

enum AverageFormula: string
{
    case Arithmetic = 'arithmetic';
    case Weighted = 'weighted';

    public function label(): string
    {
        return match ($this) {
            self::Arithmetic => 'Média Aritmética',
            self::Weighted => 'Média Ponderada',
        };
    }
}
