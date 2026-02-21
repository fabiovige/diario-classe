<?php

namespace App\Modules\Enrollment\Domain\Enums;

enum EnrollmentType: string
{
    case NewEnrollment = 'new_enrollment';
    case ReEnrollment = 're_enrollment';
    case TransferReceived = 'transfer_received';

    public function label(): string
    {
        return match ($this) {
            self::NewEnrollment => 'Matrícula Nova',
            self::ReEnrollment => 'Rematrícula',
            self::TransferReceived => 'Transferência Recebida',
        };
    }
}
