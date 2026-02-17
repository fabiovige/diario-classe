<?php

namespace App\Modules\Attendance\Domain\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case JustifiedAbsence = 'justified_absence';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Presente',
            self::Absent => 'Ausente',
            self::JustifiedAbsence => 'Falta Justificada',
            self::Excused => 'Dispensado',
        };
    }

    public function countsAsPresent(): bool
    {
        return match ($this) {
            self::Present, self::JustifiedAbsence, self::Excused => true,
            self::Absent => false,
        };
    }
}
