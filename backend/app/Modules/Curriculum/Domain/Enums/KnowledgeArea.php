<?php

namespace App\Modules\Curriculum\Domain\Enums;

enum KnowledgeArea: string
{
    case Linguagens = 'linguagens';
    case Matematica = 'matematica';
    case CienciasNatureza = 'ciencias_natureza';
    case CienciasHumanas = 'ciencias_humanas';
    case EnsinoReligioso = 'ensino_religioso';
    case ParteDiversificada = 'parte_diversificada';

    public function label(): string
    {
        return match ($this) {
            self::Linguagens => 'Linguagens',
            self::Matematica => 'Matemática',
            self::CienciasNatureza => 'Ciências da Natureza',
            self::CienciasHumanas => 'Ciências Humanas',
            self::EnsinoReligioso => 'Ensino Religioso',
            self::ParteDiversificada => 'Parte Diversificada',
        };
    }
}
