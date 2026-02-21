<?php

namespace App\Modules\SchoolStructure\Presentation\Controllers;

use App\Modules\SchoolStructure\Application\DTOs\CreateShiftDTO;
use App\Modules\SchoolStructure\Application\UseCases\CreateShiftUseCase;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use App\Modules\SchoolStructure\Presentation\Requests\CreateShiftRequest;
use App\Modules\SchoolStructure\Presentation\Resources\ShiftResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $shifts = Shift::with('school')
            ->when($request->query('school_id'), fn ($q, $schoolId) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(ShiftResource::collection($shifts)->response()->getData(true));
    }

    public function store(CreateShiftRequest $request, CreateShiftUseCase $useCase): JsonResponse
    {
        $shift = $useCase->execute(new CreateShiftDTO(
            schoolId: $request->validated('school_id'),
            name: $request->validated('name'),
            startTime: $request->validated('start_time'),
            endTime: $request->validated('end_time'),
        ));

        return $this->created(new ShiftResource($shift->load('school')));
    }

    public function show(int $id): JsonResponse
    {
        $shift = Shift::with('school')->findOrFail($id);

        return $this->success(new ShiftResource($shift));
    }

    public function destroy(int $id): JsonResponse
    {
        Shift::findOrFail($id)->delete();

        return $this->noContent();
    }
}
