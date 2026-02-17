<?php

namespace App\Modules\People\Domain\Enums;

enum Relationship: string
{
    case Father = 'pai';
    case Mother = 'mae';
    case Grandfather = 'avo';
    case Grandmother = 'avoa';
    case Uncle = 'tio';
    case Aunt = 'tia';
    case Sibling = 'irmao';
    case LegalGuardian = 'responsavel_legal';
    case Other = 'outro';
}
