<?php

namespace App\Modules\SchoolStructure\Domain\Enums;

enum GradeLevelType: string
{
    case EarlyChildhood = 'early_childhood';
    case ElementaryEarly = 'elementary_early';
    case ElementaryLate = 'elementary_late';
    case HighSchool = 'high_school';

    public function label(): string
    {
        return match ($this) {
            self::EarlyChildhood => 'Ed. Infantil',
            self::ElementaryEarly => 'Fund. Anos Iniciais',
            self::ElementaryLate => 'Fund. Anos Finais',
            self::HighSchool => 'Ensino Médio',
        };
    }

    public function teachingModel(): string
    {
        return match ($this) {
            self::EarlyChildhood, self::ElementaryEarly => 'polivalent',
            self::ElementaryLate, self::HighSchool => 'specialist',
        };
    }

    public function teachingModelLabel(): string
    {
        return match ($this) {
            self::EarlyChildhood, self::ElementaryEarly => 'Professor Polivalente',
            self::ElementaryLate, self::HighSchool => 'Professor Especialista',
        };
    }

    public function isPolivalent(): bool
    {
        return $this->teachingModel() === 'polivalent';
    }

    public function usesExperienceFields(): bool
    {
        return $this === self::EarlyChildhood;
    }

    public function usesCurricularComponents(): bool
    {
        return ! $this->usesExperienceFields();
    }
}
