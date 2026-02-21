<?php

namespace App\Modules\People\Presentation\Controllers;

use App\Modules\People\Application\DTOs\AttachGuardianDTO;
use App\Modules\People\Application\DTOs\CreateStudentDTO;
use App\Modules\People\Application\UseCases\AttachGuardianUseCase;
use App\Modules\People\Application\UseCases\CreateStudentUseCase;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\People\Presentation\Requests\AttachGuardianRequest;
use App\Modules\People\Presentation\Requests\CreateStudentRequest;
use App\Modules\People\Presentation\Requests\UpdateStudentRequest;
use App\Modules\People\Presentation\Resources\StudentResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $students = Student::with('guardians')
            ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('active'), fn ($q, $active) => $q->where('active', $active === 'true'))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(StudentResource::collection($students)->response()->getData(true));
    }

    public function store(CreateStudentRequest $request, CreateStudentUseCase $useCase): JsonResponse
    {
        $student = $useCase->execute(new CreateStudentDTO(
            name: $request->validated('name'),
            birthDate: $request->validated('birth_date'),
            gender: $request->validated('gender'),
            socialName: $request->validated('social_name'),
            raceColor: $request->validated('race_color', 'nao_declarada'),
            cpf: $request->validated('cpf'),
            rg: $request->validated('rg'),
            susNumber: $request->validated('sus_number'),
            nisNumber: $request->validated('nis_number'),
            birthCity: $request->validated('birth_city'),
            birthState: $request->validated('birth_state'),
            nationality: $request->validated('nationality', 'brasileira'),
            medicalNotes: $request->validated('medical_notes'),
            hasDisability: $request->validated('has_disability', false),
            disabilityType: $request->validated('disability_type'),
        ));

        return $this->created(new StudentResource($student));
    }

    public function show(int $id): JsonResponse
    {
        $student = Student::with('guardians')->findOrFail($id);

        return $this->success(new StudentResource($student));
    }

    public function update(UpdateStudentRequest $request, int $id): JsonResponse
    {
        $student = Student::findOrFail($id);
        $student->update($request->validated());

        return $this->success(new StudentResource($student->refresh()->load('guardians')));
    }

    public function attachGuardian(AttachGuardianRequest $request, int $studentId, AttachGuardianUseCase $useCase): JsonResponse
    {
        $useCase->execute(new AttachGuardianDTO(
            studentId: $studentId,
            guardianId: $request->validated('guardian_id'),
            relationship: $request->validated('relationship'),
            isPrimary: $request->validated('is_primary', false),
            canPickup: $request->validated('can_pickup', true),
        ));

        $student = Student::with('guardians')->findOrFail($studentId);

        return $this->success(new StudentResource($student));
    }
}
