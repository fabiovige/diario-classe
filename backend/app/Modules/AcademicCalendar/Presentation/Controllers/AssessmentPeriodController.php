<?php

namespace App\Modules\AcademicCalendar\Presentation\Controllers;

use App\Modules\AcademicCalendar\Application\DTOs\CreateAssessmentPeriodDTO;
use App\Modules\AcademicCalendar\Application\DTOs\UpdateAssessmentPeriodDTO;
use App\Modules\AcademicCalendar\Application\UseCases\CreateAssessmentPeriodUseCase;
use App\Modules\AcademicCalendar\Application\UseCases\UpdateAssessmentPeriodUseCase;
use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\AcademicCalendar\Presentation\Requests\CreateAssessmentPeriodRequest;
use App\Modules\AcademicCalendar\Presentation\Requests\UpdateAssessmentPeriodRequest;
use App\Modules\AcademicCalendar\Presentation\Resources\AssessmentPeriodResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentPeriodController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $periods = AssessmentPeriod::with('academicYear')
            ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('academic_year_id'), fn ($q, $id) => $q->where('academic_year_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderBy('number')
            ->paginate($request->query('per_page', 15));

        return $this->success(AssessmentPeriodResource::collection($periods)->response()->getData(true));
    }

    public function store(CreateAssessmentPeriodRequest $request, CreateAssessmentPeriodUseCase $useCase): JsonResponse
    {
        $period = $useCase->execute(new CreateAssessmentPeriodDTO(
            academicYearId: $request->validated('academic_year_id'),
            type: $request->validated('type'),
            number: $request->validated('number'),
            name: $request->validated('name'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
        ));

        return $this->created(new AssessmentPeriodResource($period->load('academicYear')));
    }

    public function show(int $id): JsonResponse
    {
        $period = AssessmentPeriod::with('academicYear')->findOrFail($id);

        return $this->success(new AssessmentPeriodResource($period));
    }

    public function update(UpdateAssessmentPeriodRequest $request, int $id, UpdateAssessmentPeriodUseCase $useCase): JsonResponse
    {
        $period = $useCase->execute(new UpdateAssessmentPeriodDTO(
            id: $id,
            name: $request->validated('name'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
            status: $request->validated('status'),
        ));

        return $this->success(new AssessmentPeriodResource($period->load('academicYear')));
    }

    public function destroy(int $id): JsonResponse
    {
        AssessmentPeriod::findOrFail($id)->delete();

        return $this->noContent();
    }
}
