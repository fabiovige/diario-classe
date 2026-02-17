<?php

namespace App\Modules\SchoolStructure\Presentation\Controllers;

use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Presentation\Resources\GradeLevelResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeLevelController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $levels = GradeLevel::query()
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderBy('order')
            ->get();

        return $this->success(GradeLevelResource::collection($levels));
    }

    public function show(int $id): JsonResponse
    {
        $level = GradeLevel::findOrFail($id);

        return $this->success(new GradeLevelResource($level));
    }
}
