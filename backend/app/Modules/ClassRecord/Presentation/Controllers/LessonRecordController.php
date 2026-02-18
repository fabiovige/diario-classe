<?php

namespace App\Modules\ClassRecord\Presentation\Controllers;

use App\Modules\ClassRecord\Application\DTOs\CreateLessonRecordDTO;
use App\Modules\ClassRecord\Application\DTOs\UpdateLessonRecordDTO;
use App\Modules\ClassRecord\Application\UseCases\CreateLessonRecordUseCase;
use App\Modules\ClassRecord\Application\UseCases\UpdateLessonRecordUseCase;
use App\Modules\ClassRecord\Domain\Entities\LessonRecord;
use App\Modules\ClassRecord\Presentation\Requests\CreateLessonRecordRequest;
use App\Modules\ClassRecord\Presentation\Requests\UpdateLessonRecordRequest;
use App\Modules\ClassRecord\Presentation\Resources\LessonRecordResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonRecordController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $records = LessonRecord::with(['classGroup.gradeLevel', 'classGroup.shift', 'teacherAssignment'])
            ->when($request->query('class_group_id'), fn ($q, $id) => $q->where('class_group_id', $id))
            ->when($request->query('teacher_assignment_id'), fn ($q, $id) => $q->where('teacher_assignment_id', $id))
            ->when($request->query('date_from'), fn ($q, $date) => $q->where('date', '>=', $date))
            ->when($request->query('date_to'), fn ($q, $date) => $q->where('date', '<=', $date))
            ->orderByDesc('date')
            ->paginate($request->query('per_page', 15));

        return $this->success(LessonRecordResource::collection($records)->response()->getData(true));
    }

    public function store(CreateLessonRecordRequest $request, CreateLessonRecordUseCase $useCase): JsonResponse
    {
        $record = $useCase->execute(new CreateLessonRecordDTO(
            classGroupId: $request->validated('class_group_id'),
            teacherAssignmentId: $request->validated('teacher_assignment_id'),
            date: $request->validated('date'),
            content: $request->validated('content'),
            methodology: $request->validated('methodology'),
            observations: $request->validated('observations'),
            classCount: $request->validated('class_count', 1),
        ));

        return $this->created(new LessonRecordResource($record->load(['classGroup.gradeLevel', 'classGroup.shift', 'teacherAssignment'])));
    }

    public function show(int $id): JsonResponse
    {
        $record = LessonRecord::with(['classGroup.gradeLevel', 'classGroup.shift', 'teacherAssignment'])->findOrFail($id);

        return $this->success(new LessonRecordResource($record));
    }

    public function update(UpdateLessonRecordRequest $request, int $id, UpdateLessonRecordUseCase $useCase): JsonResponse
    {
        $record = $useCase->execute(new UpdateLessonRecordDTO(
            id: $id,
            content: $request->validated('content'),
            methodology: $request->validated('methodology'),
            observations: $request->validated('observations'),
            classCount: $request->validated('class_count'),
        ));

        return $this->success(new LessonRecordResource($record->load(['classGroup.gradeLevel', 'classGroup.shift', 'teacherAssignment'])));
    }
}
