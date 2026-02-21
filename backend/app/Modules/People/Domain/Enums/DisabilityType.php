<?php

namespace App\Modules\People\Domain\Enums;

enum DisabilityType: string
{
    case Visual = 'visual';
    case Hearing = 'hearing';
    case Physical = 'physical';
    case Intellectual = 'intellectual';
    case Autism = 'autism';
    case GiftedTalented = 'gifted_talented';
    case Multiple = 'multiple';
    case Deafblind = 'deafblind';

    public function label(): string
    {
        return match ($this) {
            self::Visual => 'Deficiencia Visual',
            self::Hearing => 'Deficiencia Auditiva',
            self::Physical => 'Deficiencia Fisica',
            self::Intellectual => 'Deficiencia Intelectual',
            self::Autism => 'Transtorno do Espectro Autista (TEA)',
            self::GiftedTalented => 'Altas Habilidades/Superdotacao',
            self::Multiple => 'Deficiencia Multipla',
            self::Deafblind => 'Surdocegueira',
        };
    }
}
