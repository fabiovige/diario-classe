<?php

namespace App\Modules\Assessment\Domain\Enums;

enum RecoveryType: string
{
    case Parallel = 'parallel';
    case Continuous = 'continuous';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Parallel => 'Paralela',
            self::Continuous => 'Contínua',
            self::Final => 'Final',
        };
    }
}
