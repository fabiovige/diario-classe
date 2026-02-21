<?php

namespace App\Modules\People\Presentation\Controllers;

use App\Modules\People\Application\DTOs\CreateTeacherDTO;
use App\Modules\People\Application\UseCases\CreateTeacherUseCase;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\People\Presentation\Requests\CreateTeacherRequest;
use App\Modules\People\Presentation\Resources\TeacherResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $teachers = Teacher::with(['user', 'school'])
            ->when($request->query('search'), fn ($q, $search) => $q->whereHas('user', fn ($sub) => $sub->where('name', 'like', "%{$search}%")))
            ->when($request->query('school_id'), fn ($q, $schoolId) => $q->where('school_id', $schoolId))
            ->when($request->query('active'), fn ($q, $active) => $q->where('active', $active === 'true'))
            ->paginate($request->query('per_page', 15));

        return $this->success(TeacherResource::collection($teachers)->response()->getData(true));
    }

    public function store(CreateTeacherRequest $request, CreateTeacherUseCase $useCase): JsonResponse
    {
        $teacher = $useCase->execute(new CreateTeacherDTO(
            userId: $request->validated('user_id'),
            schoolId: $request->validated('school_id'),
            registrationNumber: $request->validated('registration_number'),
            specialization: $request->validated('specialization'),
            hireDate: $request->validated('hire_date'),
        ));

        return $this->created(new TeacherResource($teacher->load(['user', 'school'])));
    }

    public function show(int $id): JsonResponse
    {
        $teacher = Teacher::with(['user', 'school'])->findOrFail($id);

        return $this->success(new TeacherResource($teacher));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->update($request->only(['registration_number', 'specialization', 'hire_date', 'active']));

        return $this->success(new TeacherResource($teacher->refresh()->load(['user', 'school'])));
    }
}
