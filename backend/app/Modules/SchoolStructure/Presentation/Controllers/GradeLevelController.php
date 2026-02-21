<?php

namespace App\Modules\SchoolStructure\Presentation\Controllers;

use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Presentation\Requests\CreateGradeLevelRequest;
use App\Modules\SchoolStructure\Presentation\Resources\GradeLevelResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeLevelController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $levels = GradeLevel::query()
            ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderBy('order')
            ->paginate($request->query('per_page', 15));

        return $this->success(GradeLevelResource::collection($levels)->response()->getData(true));
    }

    public function store(CreateGradeLevelRequest $request): JsonResponse
    {
        $level = GradeLevel::create($request->validated());

        return $this->created(new GradeLevelResource($level));
    }

    public function show(int $id): JsonResponse
    {
        $level = GradeLevel::findOrFail($id);

        return $this->success(new GradeLevelResource($level));
    }

    public function update(CreateGradeLevelRequest $request, int $id): JsonResponse
    {
        $level = GradeLevel::findOrFail($id);
        $level->update($request->validated());

        return $this->success(new GradeLevelResource($level));
    }

    public function destroy(int $id): JsonResponse
    {
        GradeLevel::findOrFail($id)->delete();

        return $this->noContent();
    }
}
