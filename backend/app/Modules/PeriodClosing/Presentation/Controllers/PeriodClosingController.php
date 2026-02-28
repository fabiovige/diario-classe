<?php

namespace App\Modules\PeriodClosing\Presentation\Controllers;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\FinalAverage;
use App\Modules\Assessment\Domain\Entities\PeriodAverage;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\PeriodClosing\Application\UseCases\BulkTeacherClosePeriodUseCase;
use App\Modules\PeriodClosing\Application\UseCases\CalculateBulkFinalResultsUseCase;
use App\Modules\PeriodClosing\Application\UseCases\CalculateFinalResultUseCase;
use App\Modules\PeriodClosing\Application\UseCases\ClosePeriodUseCase;
use App\Modules\PeriodClosing\Application\UseCases\ReopenPeriodClosingUseCase;
use App\Modules\PeriodClosing\Application\UseCases\RequestRectificationUseCase;
use App\Modules\PeriodClosing\Application\UseCases\RunCompletenessCheckUseCase;
use App\Modules\PeriodClosing\Application\UseCases\SubmitPeriodClosingUseCase;
use App\Modules\PeriodClosing\Application\UseCases\TeacherClosePeriodUseCase;
use App\Modules\PeriodClosing\Application\UseCases\ValidatePeriodClosingUseCase;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Entities\Rectification;
use App\Modules\PeriodClosing\Presentation\Requests\RequestRectificationRequest;
use App\Modules\PeriodClosing\Presentation\Requests\ValidatePeriodClosingRequest;
use App\Modules\PeriodClosing\Presentation\Resources\FinalResultResource;
use App\Modules\PeriodClosing\Presentation\Resources\PeriodClosingResource;
use App\Modules\PeriodClosing\Presentation\Resources\RectificationResource;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodClosingController extends ApiController
{
    private const EAGER_RELATIONS = [
        'assessmentPeriod',
        'classGroup.gradeLevel',
        'classGroup.shift',
        'teacherAssignment.curricularComponent',
        'teacherAssignment.experienceField',
    ];

    public function index(Request $request): JsonResponse
    {
        $closings = PeriodClosing::with(self::EAGER_RELATIONS)
            ->when($request->query('school_id'), fn ($q, $id) => $q->whereHas('classGroup.academicYear', fn ($q2) => $q2->where('school_id', $id)))
            ->when($request->query('class_group_id'), fn ($q, $id) => $q->where('class_group_id', $id))
            ->when($request->query('assessment_period_id'), fn ($q, $id) => $q->where('assessment_period_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($request->query('per_page', 15));

        return $this->success(PeriodClosingResource::collection($closings)->response()->getData(true));
    }

    public function show(int $id): JsonResponse
    {
        $closing = PeriodClosing::with(self::EAGER_RELATIONS)->findOrFail($id);

        return $this->success(new PeriodClosingResource($closing));
    }

    public function check(int $id, RunCompletenessCheckUseCase $useCase): JsonResponse
    {
        $closing = $useCase->execute($id);

        return $this->success(new PeriodClosingResource($closing->load(self::EAGER_RELATIONS)));
    }

    public function submit(int $id, SubmitPeriodClosingUseCase $useCase): JsonResponse
    {
        $closing = $useCase->execute($id);

        return $this->success(new PeriodClosingResource($closing->load(self::EAGER_RELATIONS)));
    }

    public function validate(ValidatePeriodClosingRequest $request, int $id, ValidatePeriodClosingUseCase $useCase): JsonResponse
    {
        $closing = $useCase->execute(
            periodClosingId: $id,
            approve: $request->validated('approve'),
            rejectionReason: $request->validated('rejection_reason'),
        );

        return $this->success(new PeriodClosingResource($closing->load(self::EAGER_RELATIONS)));
    }

    public function close(int $id, ClosePeriodUseCase $useCase): JsonResponse
    {
        $closing = $useCase->execute($id);

        return $this->success(new PeriodClosingResource($closing->load(self::EAGER_RELATIONS)));
    }

    public function teacherClose(int $id, TeacherClosePeriodUseCase $useCase): JsonResponse
    {
        $closing = $useCase->execute($id);

        return $this->success(new PeriodClosingResource($closing->load(self::EAGER_RELATIONS)));
    }

    public function bulkTeacherClose(Request $request, BulkTeacherClosePeriodUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(
            classGroupId: (int) $request->input('class_group_id'),
            teacherAssignmentId: (int) $request->input('teacher_assignment_id'),
        );

        return $this->success($result);
    }

    public function reopen(Request $request, int $id, ReopenPeriodClosingUseCase $useCase): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $closing = $useCase->execute($id, $request->input('reason'));

        return $this->success(new PeriodClosingResource($closing->load(self::EAGER_RELATIONS)));
    }

    public function pendencies(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|integer',
            'academic_year_id' => 'required|integer',
        ]);

        $rows = DB::table('period_closings as pc')
            ->join('teacher_assignments as ta', 'pc.teacher_assignment_id', '=', 'ta.id')
            ->join('teachers as t', 'ta.teacher_id', '=', 't.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->join('class_groups as cg', 'pc.class_group_id', '=', 'cg.id')
            ->join('academic_years as ay', 'cg.academic_year_id', '=', 'ay.id')
            ->join('assessment_periods as ap', 'pc.assessment_period_id', '=', 'ap.id')
            ->leftJoin('curricular_components as cc', 'ta.curricular_component_id', '=', 'cc.id')
            ->leftJoin('experience_fields as ef', 'ta.experience_field_id', '=', 'ef.id')
            ->where('ay.school_id', $request->query('school_id'))
            ->where('cg.academic_year_id', $request->query('academic_year_id'))
            ->where('pc.status', '!=', ClosingStatus::Closed->value)
            ->select([
                't.id as teacher_id',
                'u.name as teacher_name',
                'pc.id as closing_id',
                'cg.name as class_group_name',
                DB::raw('COALESCE(cc.name, ef.name) as subject_name'),
                'ap.name as period_name',
                'pc.status',
                'pc.all_grades_complete',
                'pc.all_attendance_complete',
                'pc.all_lesson_records_complete',
            ])
            ->orderBy('u.name')
            ->orderBy('cg.name')
            ->orderBy('ap.number')
            ->get();

        $grouped = $rows->groupBy('teacher_id')->map(function ($closings) {
            $first = $closings->first();

            return [
                'teacher_id' => $first->teacher_id,
                'teacher_name' => $first->teacher_name,
                'total_pending' => $closings->count(),
                'closings' => $closings->map(fn ($c) => [
                    'id' => $c->closing_id,
                    'class_group' => $c->class_group_name,
                    'subject' => $c->subject_name,
                    'period' => $c->period_name,
                    'status' => $c->status,
                    'grades_complete' => (bool) $c->all_grades_complete,
                    'attendance_complete' => (bool) $c->all_attendance_complete,
                    'lesson_records_complete' => (bool) $c->all_lesson_records_complete,
                ])->values(),
            ];
        })->values();

        return $this->success($grouped);
    }

    public function myClosings(Request $request): JsonResponse
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return $this->success([]);
        }

        $assignmentIds = TeacherAssignment::where('teacher_id', $teacher->id)
            ->pluck('id');

        $closings = PeriodClosing::with(self::EAGER_RELATIONS)
            ->whereIn('teacher_assignment_id', $assignmentIds)
            ->when($request->query('academic_year_id'), fn ($q, $id) => $q->whereHas('classGroup', fn ($q2) => $q2->where('academic_year_id', $id)))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get();

        return $this->success(PeriodClosingResource::collection($closings));
    }

    public function classGroupStatus(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|integer',
        ]);

        $academicYearId = (int) $request->query('academic_year_id');

        $classGroups = ClassGroup::with(['gradeLevel', 'shift'])
            ->where('academic_year_id', $academicYearId)
            ->orderBy('name')
            ->get();

        $closingCounts = DB::table('period_closings')
            ->whereIn('class_group_id', $classGroups->pluck('id'))
            ->select([
                'class_group_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count"),
            ])
            ->groupBy('class_group_id')
            ->get()
            ->keyBy('class_group_id');

        $studentCounts = DB::table('class_assignments')
            ->join('enrollments', 'class_assignments.enrollment_id', '=', 'enrollments.id')
            ->whereIn('class_assignments.class_group_id', $classGroups->pluck('id'))
            ->where('class_assignments.status', 'active')
            ->where('enrollments.status', 'active')
            ->select([
                'class_assignments.class_group_id',
                DB::raw('COUNT(DISTINCT enrollments.student_id) as total_students'),
            ])
            ->groupBy('class_assignments.class_group_id')
            ->get()
            ->keyBy('class_group_id');

        $resultCounts = DB::table('final_results')
            ->where('academic_year_id', $academicYearId)
            ->whereIn('class_group_id', $classGroups->pluck('id'))
            ->select([
                'class_group_id',
                DB::raw('COUNT(DISTINCT student_id) as students_with_results'),
            ])
            ->groupBy('class_group_id')
            ->get()
            ->keyBy('class_group_id');

        $data = $classGroups->map(function ($cg) use ($closingCounts, $studentCounts, $resultCounts) {
            $closing = $closingCounts->get($cg->id);
            $students = $studentCounts->get($cg->id);
            $results = $resultCounts->get($cg->id);

            $totalClosings = $closing ? (int) $closing->total : 0;
            $closedClosings = $closing ? (int) $closing->closed_count : 0;
            $totalStudents = $students ? (int) $students->total_students : 0;
            $studentsWithResults = $results ? (int) $results->students_with_results : 0;

            $allClosingsDone = $totalClosings > 0 && $closedClosings === $totalClosings;
            $allResultsDone = $totalStudents > 0 && $studentsWithResults >= $totalStudents;

            return [
                'class_group_id' => $cg->id,
                'name' => $cg->name,
                'grade_level' => $cg->gradeLevel?->name,
                'shift' => $cg->shift?->name?->label(),
                'total_closings' => $totalClosings,
                'closed_closings' => $closedClosings,
                'total_students' => $totalStudents,
                'students_with_results' => $studentsWithResults,
                'ready' => $allClosingsDone && $allResultsDone,
            ];
        });

        return $this->success($data);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $query = DB::table('period_closings')
            ->when($request->query('school_id'), function ($q, $schoolId) {
                $q->whereIn('class_group_id', function ($sub) use ($schoolId) {
                    $sub->select('id')
                        ->from('class_groups')
                        ->whereIn('academic_year_id', function ($sub2) use ($schoolId) {
                            $sub2->select('id')
                                ->from('academic_years')
                                ->where('school_id', $schoolId);
                        });
                });
            });

        $counts = (clone $query)
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'total' => $counts->sum(),
            'pending' => (int) ($counts['pending'] ?? 0),
            'in_validation' => (int) ($counts['in_validation'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'closed' => (int) ($counts['closed'] ?? 0),
        ];

        return $this->success($summary);
    }

    public function storeRectification(RequestRectificationRequest $request, RequestRectificationUseCase $useCase): JsonResponse
    {
        $rectification = $useCase->execute(
            periodClosingId: $request->validated('period_closing_id'),
            entityType: $request->validated('entity_type'),
            entityId: $request->validated('entity_id'),
            fieldChanged: $request->validated('field_changed'),
            oldValue: $request->validated('old_value'),
            newValue: $request->validated('new_value'),
            justification: $request->validated('justification'),
        );

        return $this->created(new RectificationResource($rectification));
    }

    public function approveRectification(Request $request, int $id): JsonResponse
    {
        $rectification = Rectification::findOrFail($id);

        $approve = $request->boolean('approve', true);
        $rectification->update([
            'status' => $approve ? 'approved' : 'rejected',
            'approved_by' => auth()->id(),
        ]);

        return $this->success(new RectificationResource($rectification->refresh()));
    }

    public function calculateFinalResult(Request $request, CalculateFinalResultUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(
            studentId: (int) $request->input('student_id'),
            classGroupId: (int) $request->input('class_group_id'),
            academicYearId: (int) $request->input('academic_year_id'),
        );

        return $this->success(new FinalResultResource($result));
    }

    public function studentFinalResult(int $studentId): JsonResponse
    {
        $results = DB::table('final_results')
            ->where('student_id', $studentId)
            ->get();

        return $this->success($results);
    }

    public function classGroupFinalResults(int $classGroupId): JsonResponse
    {
        $classGroup = ClassGroup::with(['academicYear.school', 'gradeLevel', 'shift'])->findOrFail($classGroupId);
        $academicYearId = $classGroup->academic_year_id;

        $periods = AssessmentPeriod::where('academic_year_id', $academicYearId)
            ->orderBy('number')
            ->get();

        $assignments = TeacherAssignment::where('class_group_id', $classGroupId)
            ->where('active', true)
            ->with(['curricularComponent', 'experienceField'])
            ->get();

        $config = AssessmentConfig::where('school_id', $classGroup->academicYear->school_id)
            ->where('academic_year_id', $academicYearId)
            ->where('grade_level_id', $classGroup->grade_level_id)
            ->first();

        $studentIds = ClassAssignment::where('class_assignments.class_group_id', $classGroupId)
            ->where('class_assignments.status', 'active')
            ->join('enrollments', 'class_assignments.enrollment_id', '=', 'enrollments.id')
            ->where('enrollments.status', 'active')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->orderBy('students.name')
            ->pluck('enrollments.student_id')
            ->unique();

        $allPeriodAverages = PeriodAverage::where('class_group_id', $classGroupId)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        $allFinalAverages = FinalAverage::where('class_group_id', $classGroupId)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        $allFinalResults = DB::table('final_results')
            ->where('class_group_id', $classGroupId)
            ->where('academic_year_id', $academicYearId)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        $students = \App\Modules\People\Domain\Entities\Student::whereIn('id', $studentIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $studentsData = [];
        $summary = ['total' => 0, 'approved' => 0, 'retained' => 0, 'pending' => 0];

        foreach ($studentIds as $studentId) {
            $student = $students->get($studentId);
            if (! $student) {
                continue;
            }

            $studentPeriodAverages = $allPeriodAverages->get($studentId, collect());
            $studentFinalAverages = $allFinalAverages->get($studentId, collect())->keyBy('teacher_assignment_id');
            $finalResult = $allFinalResults->get($studentId);

            $subjectsData = [];
            foreach ($assignments as $assignment) {
                $averages = $studentPeriodAverages->where('teacher_assignment_id', $assignment->id);
                /** @var ?FinalAverage $final */
                $final = $studentFinalAverages->get($assignment->id);

                $periodsMap = [];
                foreach ($periods as $period) {
                    /** @var ?PeriodAverage $avg */
                    $avg = $averages->firstWhere('assessment_period_id', $period->id);
                    $periodsMap[(string) $period->number] = $avg !== null && $avg->numeric_average !== null ? (float) $avg->numeric_average : null;
                }

                $subjectName = $assignment->curricularComponent !== null
                    ? $assignment->curricularComponent->name
                    : ($assignment->experienceField !== null ? $assignment->experienceField->name : '');

                $subjectsData[] = [
                    'name' => $subjectName,
                    'periods' => $periodsMap,
                    'final_average' => $final !== null && $final->numeric_average !== null ? (float) $final->numeric_average : null,
                    'status' => $final !== null ? $final->status : 'pending',
                ];
            }

            $resultValue = $finalResult !== null ? ($finalResult->result ?: null) : null;

            $summary['total']++;
            if ($resultValue === 'approved') {
                $summary['approved']++;
            } elseif ($resultValue !== null) {
                $summary['retained']++;
            } else {
                $summary['pending']++;
            }

            $studentsData[] = [
                'student_id' => $studentId,
                'name' => $student->name,
                'subjects' => $subjectsData,
                'overall_average' => $finalResult !== null && $finalResult->overall_average !== null ? (float) $finalResult->overall_average : null,
                'overall_frequency' => $finalResult !== null && $finalResult->overall_frequency !== null ? (float) $finalResult->overall_frequency : null,
                'result' => $resultValue,
            ];
        }

        $summary['passing_grade'] = $config ? (float) $config->passing_grade : 6.0;

        return $this->success([
            'class_group' => [
                'id' => $classGroup->id,
                'name' => $classGroup->name,
                'grade_level' => $classGroup->gradeLevel?->name,
                'shift' => $classGroup->shift?->name?->label(),
                'academic_year' => [
                    'id' => $classGroup->academicYear->id,
                    'year' => $classGroup->academicYear->year,
                    'status' => $classGroup->academicYear->status,
                    'school_name' => $classGroup->academicYear->school?->name,
                ],
            ],
            'assessment_periods' => $periods->map(fn (AssessmentPeriod $p) => [
                'id' => $p->id,
                'number' => $p->number,
                'name' => $p->name,
            ])->values(),
            'students' => $studentsData,
            'summary' => $summary,
        ]);
    }

    public function calculateBulkFinalResults(Request $request, CalculateBulkFinalResultsUseCase $useCase): JsonResponse
    {
        $results = $useCase->execute(
            classGroupId: (int) $request->input('class_group_id'),
            academicYearId: (int) $request->input('academic_year_id'),
        );

        return $this->success(FinalResultResource::collection($results));
    }
}
