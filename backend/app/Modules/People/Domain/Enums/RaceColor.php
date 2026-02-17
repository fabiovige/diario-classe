<?php

namespace App\Modules\People\Domain\Enums;

enum RaceColor: string
{
    case White = 'branca';
    case Black = 'preta';
    case Brown = 'parda';
    case Yellow = 'amarela';
    case Indigenous = 'indigena';
    case NotDeclared = 'nao_declarada';
}
