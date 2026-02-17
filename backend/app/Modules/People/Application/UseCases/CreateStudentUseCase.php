<?php

namespace App\Modules\People\Application\UseCases;

use App\Modules\People\Application\DTOs\CreateStudentDTO;
use App\Modules\People\Domain\Entities\Student;

final class CreateStudentUseCase
{
    public function execute(CreateStudentDTO $dto): Student
    {
        return Student::create([
            'name' => $dto->name,
            'social_name' => $dto->socialName,
            'birth_date' => $dto->birthDate,
            'gender' => $dto->gender,
            'race_color' => $dto->raceColor,
            'cpf' => $dto->cpf,
            'rg' => $dto->rg,
            'sus_number' => $dto->susNumber,
            'nis_number' => $dto->nisNumber,
            'birth_city' => $dto->birthCity,
            'birth_state' => $dto->birthState,
            'nationality' => $dto->nationality,
            'medical_notes' => $dto->medicalNotes,
            'has_disability' => $dto->hasDisability,
            'disability_type' => $dto->disabilityType,
        ]);
    }
}
