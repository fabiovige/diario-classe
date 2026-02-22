<?php

namespace App\Modules\Curriculum\Presentation\Controllers;

use App\Modules\Curriculum\Application\DTOs\CreateTeacherAssignmentDTO;
use App\Modules\Curriculum\Application\UseCases\CreateTeacherAssignmentUseCase;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Curriculum\Presentation\Requests\CreateTeacherAssignmentRequest;
use App\Modules\Curriculum\Presentation\Requests\UpdateTeacherAssignmentRequest;
use App\Modules\Curriculum\Presentation\Resources\TeacherAssignmentResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAssignmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $assignments = TeacherAssignment::with(['teacher.user', 'classGroup.gradeLevel', 'classGroup.shift', 'curricularComponent', 'experienceField'])
            ->when($request->query('school_id'), fn ($q, $id) => $q->whereHas('classGroup.academicYear', fn ($q2) => $q2->where('school_id', $id)))
            ->when($request->query('search'), fn ($q, $search) => $q->whereHas('teacher.user', fn ($q2) => $q2->where('name', 'like', "%{$search}%")))
            ->when($request->query('teacher_id'), fn ($q, $id) => $q->where('teacher_id', $id))
            ->when($request->query('class_group_id'), fn ($q, $id) => $q->where('class_group_id', $id))
            ->when($request->query('active') !== null, fn ($q) => $q->where('active', $request->boolean('active')))
            ->orderByDesc('start_date')
            ->paginate($request->query('per_page', 15));

        return $this->success(TeacherAssignmentResource::collection($assignments)->response()->getData(true));
    }

    public function store(CreateTeacherAssignmentRequest $request, CreateTeacherAssignmentUseCase $useCase): JsonResponse
    {
        $assignment = $useCase->execute(new CreateTeacherAssignmentDTO(
            teacherId: $request->validated('teacher_id'),
            classGroupId: $request->validated('class_group_id'),
            curricularComponentId: $request->validated('curricular_component_id'),
            experienceFieldId: $request->validated('experience_field_id'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
        ));

        return $this->created(new TeacherAssignmentResource(
            $assignment->load(['teacher.user', 'classGroup.gradeLevel', 'classGroup.shift', 'curricularComponent', 'experienceField'])
        ));
    }

    public function show(int $id): JsonResponse
    {
        $assignment = TeacherAssignment::with(['teacher.user', 'classGroup.gradeLevel', 'classGroup.shift', 'curricularComponent', 'experienceField'])
            ->findOrFail($id);

        return $this->success(new TeacherAssignmentResource($assignment));
    }

    public function update(UpdateTeacherAssignmentRequest $request, int $id): JsonResponse
    {
        $assignment = TeacherAssignment::findOrFail($id);

        $assignment->update($request->validated());

        return $this->success(new TeacherAssignmentResource(
            $assignment->refresh()->load(['teacher.user', 'classGroup.gradeLevel', 'classGroup.shift', 'curricularComponent', 'experienceField'])
        ));
    }

    public function destroy(int $id): JsonResponse
    {
        TeacherAssignment::findOrFail($id)->delete();

        return $this->noContent();
    }
}
