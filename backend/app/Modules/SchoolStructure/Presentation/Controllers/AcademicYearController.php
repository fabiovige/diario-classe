<?php

namespace App\Modules\SchoolStructure\Presentation\Controllers;

use App\Modules\SchoolStructure\Application\DTOs\CreateAcademicYearDTO;
use App\Modules\SchoolStructure\Application\UseCases\CloseAcademicYearUseCase;
use App\Modules\SchoolStructure\Application\UseCases\CreateAcademicYearUseCase;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Presentation\Requests\CreateAcademicYearRequest;
use App\Modules\SchoolStructure\Presentation\Resources\AcademicYearResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicYearController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $years = AcademicYear::with('school')
            ->when($request->query('search'), fn ($q, $search) => $q->where('year', 'like', "%{$search}%"))
            ->when($request->query('school_id'), fn ($q, $schoolId) => $q->where('school_id', $schoolId))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('year')
            ->paginate($request->query('per_page', 15));

        return $this->success(AcademicYearResource::collection($years)->response()->getData(true));
    }

    public function store(CreateAcademicYearRequest $request, CreateAcademicYearUseCase $useCase): JsonResponse
    {
        $year = $useCase->execute(new CreateAcademicYearDTO(
            schoolId: $request->validated('school_id'),
            year: $request->validated('year'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
            status: $request->validated('status', 'planning'),
        ));

        return $this->created(new AcademicYearResource($year->load('school')));
    }

    public function show(int $id): JsonResponse
    {
        $year = AcademicYear::with(['school', 'classGroups'])->findOrFail($id);

        return $this->success(new AcademicYearResource($year));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $year = AcademicYear::findOrFail($id);
        $year->update($request->only(['status', 'start_date', 'end_date']));

        return $this->success(new AcademicYearResource($year->refresh()->load('school')));
    }

    public function destroy(int $id): JsonResponse
    {
        AcademicYear::findOrFail($id)->delete();

        return $this->noContent();
    }

    public function close(int $id, CloseAcademicYearUseCase $useCase): JsonResponse
    {
        $year = $useCase->execute($id);

        return $this->success(new AcademicYearResource($year));
    }
}
