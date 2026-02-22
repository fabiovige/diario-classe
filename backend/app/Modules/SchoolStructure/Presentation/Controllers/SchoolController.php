<?php

namespace App\Modules\SchoolStructure\Presentation\Controllers;

use App\Modules\SchoolStructure\Application\DTOs\CreateSchoolDTO;
use App\Modules\SchoolStructure\Application\UseCases\CreateSchoolUseCase;
use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\SchoolStructure\Presentation\Requests\CreateSchoolRequest;
use App\Modules\SchoolStructure\Presentation\Requests\UpdateSchoolRequest;
use App\Modules\SchoolStructure\Presentation\Resources\SchoolResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $schools = School::query()
            ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('active'), fn ($q, $active) => $q->where('active', $active === 'true'))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(SchoolResource::collection($schools)->response()->getData(true));
    }

    public function store(CreateSchoolRequest $request, CreateSchoolUseCase $useCase): JsonResponse
    {
        $school = $useCase->execute(new CreateSchoolDTO(
            name: $request->validated('name'),
            inepCode: $request->validated('inep_code'),
            address: $request->validated('address'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
        ));

        return $this->created(new SchoolResource($school));
    }

    public function show(int $id): JsonResponse
    {
        $school = School::with(['shifts', 'academicYears'])->findOrFail($id);

        return $this->success(new SchoolResource($school));
    }

    public function update(UpdateSchoolRequest $request, int $id): JsonResponse
    {
        $school = School::findOrFail($id);
        $school->update($request->validated());

        return $this->success(new SchoolResource($school->refresh()));
    }

    public function destroy(int $id): JsonResponse
    {
        $school = School::findOrFail($id);
        $school->update(['active' => false]);

        return $this->noContent();
    }
}
