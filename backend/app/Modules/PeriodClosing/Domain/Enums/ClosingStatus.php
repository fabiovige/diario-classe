<?php

namespace App\Modules\PeriodClosing\Domain\Enums;

enum ClosingStatus: string
{
    case Pending = 'pending';
    case InValidation = 'in_validation';
    case Approved = 'approved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::InValidation => 'Em Validação',
            self::Approved => 'Aprovado',
            self::Closed => 'Fechado',
        };
    }

    /** @return array<ClosingStatus> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::InValidation],
            self::InValidation => [self::Pending, self::Approved],
            self::Approved => [self::Closed],
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
