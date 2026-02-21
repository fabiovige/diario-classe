<?php

namespace App\Modules\Curriculum\Presentation\Controllers;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\ClassRecord\Domain\Entities\LessonRecord;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Curriculum\Presentation\Requests\DailySummaryRequest;
use App\Modules\Curriculum\Presentation\Resources\DailyAssignmentResource;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class DailyClassController extends ApiController
{
    public function index(DailySummaryRequest $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        $date = $request->validated('date');

        $assignments = TeacherAssignment::query()
            ->select('teacher_assignments.*')
            ->join('class_groups', 'class_groups.id', '=', 'teacher_assignments.class_group_id')
            ->with([
                'teacher.user',
                'classGroup.gradeLevel',
                'classGroup.shift',
                'classGroup.academicYear',
                'curricularComponent',
                'experienceField',
            ])
            ->where('teacher_assignments.teacher_id', $teacher->id)
            ->where('teacher_assignments.active', true)
            ->addSelect([
                'has_attendance' => AttendanceRecord::query()
                    ->selectRaw('1')
                    ->whereColumn('attendance_records.teacher_assignment_id', 'teacher_assignments.id')
                    ->whereDate('attendance_records.date', $date)
                    ->limit(1),
                'has_lesson_record' => LessonRecord::query()
                    ->selectRaw('1')
                    ->whereColumn('lesson_records.teacher_assignment_id', 'teacher_assignments.id')
                    ->whereDate('lesson_records.date', $date)
                    ->limit(1),
                'has_open_period' => AssessmentPeriod::query()
                    ->selectRaw('1')
                    ->whereColumn('assessment_periods.academic_year_id', 'class_groups.academic_year_id')
                    ->where('assessment_periods.status', 'open')
                    ->whereDate('assessment_periods.start_date', '<=', $date)
                    ->whereDate('assessment_periods.end_date', '>=', $date)
                    ->limit(1),
            ])
            ->get();

        return $this->success(DailyAssignmentResource::collection($assignments));
    }

    private function resolveTeacher(DailySummaryRequest $request): Teacher
    {
        $user = $request->user();
        $roleSlug = $user?->role?->slug;

        if ($request->has('teacher_id') && in_array($roleSlug, [RoleSlug::Admin, RoleSlug::Director, RoleSlug::Coordinator], true)) {
            return Teacher::findOrFail($request->validated('teacher_id'));
        }

        return Teacher::where('user_id', $user?->id)->firstOrFail();
    }
}
