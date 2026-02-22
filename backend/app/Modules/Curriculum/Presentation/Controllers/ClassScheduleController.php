<?php

namespace App\Modules\Curriculum\Presentation\Controllers;

use App\Modules\Curriculum\Application\DTOs\SaveClassScheduleDTO;
use App\Modules\Curriculum\Application\UseCases\SaveClassScheduleUseCase;
use App\Modules\Curriculum\Domain\Entities\ClassSchedule;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Curriculum\Presentation\Requests\SaveClassScheduleRequest;
use App\Modules\Curriculum\Presentation\Resources\ClassScheduleResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassScheduleController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = ClassSchedule::with([
            'timeSlot.shift',
            'teacherAssignment.teacher.user',
            'teacherAssignment.classGroup.gradeLevel',
            'teacherAssignment.classGroup.shift',
            'teacherAssignment.curricularComponent',
            'teacherAssignment.experienceField',
        ]);

        if ($request->query('class_group_id')) {
            $assignmentIds = TeacherAssignment::where('class_group_id', $request->query('class_group_id'))
                ->pluck('id');
            $query->whereIn('teacher_assignment_id', $assignmentIds);
        }

        if ($request->query('teacher_id')) {
            $assignmentIds = TeacherAssignment::where('teacher_id', $request->query('teacher_id'))
                ->pluck('id');
            $query->whereIn('teacher_assignment_id', $assignmentIds);
        }

        $schedules = $query->orderBy('day_of_week')
            ->orderBy('time_slot_id')
            ->get();

        return $this->success(ClassScheduleResource::collection($schedules));
    }

    public function saveForAssignment(
        SaveClassScheduleRequest $request,
        int $teacherAssignmentId,
        SaveClassScheduleUseCase $useCase,
    ): JsonResponse {
        $useCase->execute(new SaveClassScheduleDTO(
            teacherAssignmentId: $teacherAssignmentId,
            slots: $request->validated('slots'),
        ));

        $schedules = ClassSchedule::with([
            'timeSlot.shift',
            'teacherAssignment.teacher.user',
            'teacherAssignment.classGroup.gradeLevel',
            'teacherAssignment.curricularComponent',
            'teacherAssignment.experienceField',
        ])->where('teacher_assignment_id', $teacherAssignmentId)->get();

        return $this->success(ClassScheduleResource::collection($schedules));
    }
}
