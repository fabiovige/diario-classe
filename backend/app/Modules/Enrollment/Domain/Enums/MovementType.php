<?php

namespace App\Modules\Enrollment\Domain\Enums;

enum MovementType: string
{
    case InitialEnrollment = 'matricula_inicial';
    case InternalTransfer = 'transferencia_interna';
    case ExternalTransfer = 'transferencia_externa';
    case Abandonment = 'abandono';
    case Death = 'falecimento';
    case Reclassification = 'reclassificacao';
    case Cancellation = 'cancelamento';
}
