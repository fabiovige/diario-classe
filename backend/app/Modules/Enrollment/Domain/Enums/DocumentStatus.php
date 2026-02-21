<?php

namespace App\Modules\Enrollment\Domain\Enums;

enum DocumentStatus: string
{
    case NotUploaded = 'not_uploaded';
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::NotUploaded => 'Não Enviado',
            self::PendingReview => 'Aguardando Revisão',
            self::Approved => 'Aprovado',
            self::Rejected => 'Rejeitado',
        };
    }
}
