<?php

namespace App\Modules\Identity\Domain\Enums;

enum RoleSlug: string
{
    case Admin = 'admin';
    case Director = 'director';
    case Secretary = 'secretary';
    case Coordinator = 'coordinator';
    case Teacher = 'teacher';
    case Guardian = 'guardian';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Director => 'Diretor(a)',
            self::Secretary => 'Secretário(a)',
            self::Coordinator => 'Coordenador(a)',
            self::Teacher => 'Professor(a)',
            self::Guardian => 'Responsável',
        };
    }
}
