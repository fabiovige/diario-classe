<?php

namespace App\Modules\SchoolStructure\Domain\Enums;

enum SchoolType: string
{
    case Municipal = 'municipal';
    case State = 'state';
    case Federal = 'federal';
    case Private = 'private';
}
