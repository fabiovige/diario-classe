<?php

namespace App\Modules\SchoolStructure\Presentation\Controllers;

use App\Modules\SchoolStructure\Application\DTOs\CreateClassGroupDTO;
use App\Modules\SchoolStructure\Application\UseCases\CreateClassGroupUseCase;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Presentation\Requests\CreateClassGroupRequest;
use App\Modules\SchoolStructure\Presentation\Resources\ClassGroupResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassGroupController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $groups = ClassGroup::with(['academicYear', 'gradeLevel', 'shift'])
            ->withCount('activeClassAssignments')
            ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('academic_year_id'), fn ($q, $id) => $q->where('academic_year_id', $id))
            ->when($request->query('grade_level_id'), fn ($q, $id) => $q->where('grade_level_id', $id))
            ->when($request->query('shift_id'), fn ($q, $id) => $q->where('shift_id', $id))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(ClassGroupResource::collection($groups)->response()->getData(true));
    }

    public function store(CreateClassGroupRequest $request, CreateClassGroupUseCase $useCase): JsonResponse
    {
        $group = $useCase->execute(new CreateClassGroupDTO(
            academicYearId: $request->validated('academic_year_id'),
            gradeLevelId: $request->validated('grade_level_id'),
            shiftId: $request->validated('shift_id'),
            name: $request->validated('name'),
            maxStudents: $request->validated('max_students', 30),
        ));

        return $this->created(new ClassGroupResource($group->load(['academicYear', 'gradeLevel', 'shift'])));
    }

    public function show(int $id): JsonResponse
    {
        $group = ClassGroup::with(['academicYear', 'gradeLevel', 'shift'])->findOrFail($id);

        return $this->success(new ClassGroupResource($group));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $group = ClassGroup::findOrFail($id);
        $group->update($request->only(['name', 'max_students']));

        return $this->success(new ClassGroupResource($group->refresh()->load(['academicYear', 'gradeLevel', 'shift'])));
    }

    public function destroy(int $id): JsonResponse
    {
        ClassGroup::findOrFail($id)->delete();

        return $this->noContent();
    }
}
