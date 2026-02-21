<?php

namespace App\Modules\Curriculum\Presentation\Controllers;

use App\Modules\Curriculum\Application\DTOs\CreateExperienceFieldDTO;
use App\Modules\Curriculum\Application\UseCases\CreateExperienceFieldUseCase;
use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use App\Modules\Curriculum\Presentation\Requests\CreateExperienceFieldRequest;
use App\Modules\Curriculum\Presentation\Resources\ExperienceFieldResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienceFieldController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $fields = ExperienceField::query()
            ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('active') !== null, fn ($q) => $q->where('active', $request->boolean('active')))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(ExperienceFieldResource::collection($fields)->response()->getData(true));
    }

    public function store(CreateExperienceFieldRequest $request, CreateExperienceFieldUseCase $useCase): JsonResponse
    {
        $field = $useCase->execute(new CreateExperienceFieldDTO(
            name: $request->validated('name'),
            code: $request->validated('code'),
        ));

        return $this->created(new ExperienceFieldResource($field));
    }

    public function show(int $id): JsonResponse
    {
        $field = ExperienceField::findOrFail($id);

        return $this->success(new ExperienceFieldResource($field));
    }

    public function update(CreateExperienceFieldRequest $request, int $id): JsonResponse
    {
        $field = ExperienceField::findOrFail($id);
        $field->update($request->validated());

        return $this->success(new ExperienceFieldResource($field));
    }

    public function destroy(int $id): JsonResponse
    {
        ExperienceField::findOrFail($id)->delete();

        return $this->noContent();
    }
}
