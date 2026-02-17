<?php

namespace App\Modules\People\Application\DTOs;

final readonly class CreateStudentDTO
{
    public function __construct(
        public string $name,
        public string $birthDate,
        public string $gender,
        public ?string $socialName = null,
        public string $raceColor = 'nao_declarada',
        public ?string $cpf = null,
        public ?string $rg = null,
        public ?string $susNumber = null,
        public ?string $nisNumber = null,
        public ?string $birthCity = null,
        public ?string $birthState = null,
        public string $nationality = 'brasileira',
        public ?string $medicalNotes = null,
        public bool $hasDisability = false,
        public ?string $disabilityType = null,
    ) {}
}
