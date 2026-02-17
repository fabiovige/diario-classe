<?php

namespace App\Modules\Assessment\Domain\Enums;

enum RecoveryReplaces: string
{
    case Higher = 'higher';
    case Average = 'average';
    case Last = 'last';

    public function label(): string
    {
        return match ($this) {
            self::Higher => 'Maior Nota',
            self::Average => 'Média entre Original e Recuperação',
            self::Last => 'Última Nota',
        };
    }
}
