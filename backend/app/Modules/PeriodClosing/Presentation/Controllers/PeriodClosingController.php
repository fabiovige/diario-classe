<?php

namespace App\Modules\PeriodClosing\Presentation\Controllers;

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
}
