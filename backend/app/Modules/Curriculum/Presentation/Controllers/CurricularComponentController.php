<?php

namespace App\Modules\Curriculum\Presentation\Controllers;

use App\Modules\Curriculum\Application\DTOs\CreateCurricularComponentDTO;
use App\Modules\Curriculum\Application\UseCases\CreateCurricularComponentUseCase;
use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Presentation\Requests\CreateCurricularComponentRequest;
use App\Modules\Curriculum\Presentation\Resources\CurricularComponentResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurricularComponentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $components = CurricularComponent::query()
            ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('knowledge_area'), fn ($q, $area) => $q->where('knowledge_area', $area))
            ->when($request->query('active') !== null, fn ($q) => $q->where('active', $request->boolean('active')))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(CurricularComponentResource::collection($components)->response()->getData(true));
    }

    public function store(CreateCurricularComponentRequest $request, CreateCurricularComponentUseCase $useCase): JsonResponse
    {
        $component = $useCase->execute(new CreateCurricularComponentDTO(
            name: $request->validated('name'),
            knowledgeArea: $request->validated('knowledge_area'),
            code: $request->validated('code'),
        ));

        return $this->created(new CurricularComponentResource($component));
    }

    public function show(int $id): JsonResponse
    {
        $component = CurricularComponent::findOrFail($id);

        return $this->success(new CurricularComponentResource($component));
    }

    public function update(CreateCurricularComponentRequest $request, int $id): JsonResponse
    {
        $component = CurricularComponent::findOrFail($id);
        $component->update($request->validated());

        return $this->success(new CurricularComponentResource($component));
    }

    public function destroy(int $id): JsonResponse
    {
        CurricularComponent::findOrFail($id)->delete();

        return $this->noContent();
    }
}
