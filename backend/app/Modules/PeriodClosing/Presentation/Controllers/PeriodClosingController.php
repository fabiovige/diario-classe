<?php

namespace App\Modules\PeriodClosing\Presentation\Controllers;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\FinalAverage;
use App\Modules\Assessment\Domain\Entities\PeriodAverage;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\PeriodClosing\Application\UseCases\CalculateBulkFinalResultsUseCase;
use App\Modules\PeriodClosing\Application\UseCases\CalculateFinalResultUseCase;
use App\Modules\PeriodClosing\Application\UseCases\ClosePeriodUseCase;
use App\Modules\PeriodClosing\Application\UseCases\RequestRectificationUseCase;
use App\Modules\PeriodClosing\Application\UseCases\RunCompletenessCheckUseCase;
use App\Modules\PeriodClosing\Application\UseCases\SubmitPeriodClosingUseCase;
use App\Modules\PeriodClosing\Application\UseCases\ValidatePeriodClosingUseCase;
use App\Modules\PeriodClosing\Domain\Entities\FinalResultRecord;
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

    public function dashboard(Request $request): JsonResponse
    {
        $closings = PeriodClosing::with('assessmentPeriod')
            ->when($request->query('school_id'), function ($q, $schoolId) {
                $q->whereHas('classGroup.academicYear', fn ($sub) => $sub->where('school_id', $schoolId));
            })
            ->get();

        $summary = [
            'total' => $closings->count(),
            'pending' => $closings->where('status.value', 'pending')->count(),
            'in_validation' => $closings->where('status.value', 'in_validation')->count(),
            'approved' => $closings->where('status.value', 'approved')->count(),
            'closed' => $closings->where('status.value', 'closed')->count(),
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
        $results = FinalResultRecord::where('student_id', $studentId)
            ->with(['classGroup', 'academicYear'])
            ->get();

        return $this->success(FinalResultResource::collection($results));
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

        $allFinalResults = FinalResultRecord::where('class_group_id', $classGroupId)
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

            /** @var string|null $resultValue */
            $resultValue = $finalResult !== null ? (string) ($finalResult->getRawOriginal('result') ?? '') : null;
            if ($resultValue === '') {
                $resultValue = null;
            }

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
