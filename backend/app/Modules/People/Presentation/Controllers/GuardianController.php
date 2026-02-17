<?php

namespace App\Modules\People\Presentation\Controllers;

use App\Modules\People\Application\DTOs\CreateGuardianDTO;
use App\Modules\People\Application\UseCases\CreateGuardianUseCase;
use App\Modules\People\Domain\Entities\Guardian;
use App\Modules\People\Presentation\Requests\CreateGuardianRequest;
use App\Modules\People\Presentation\Resources\GuardianResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $guardians = Guardian::with('students')
            ->when($request->query('name'), fn ($q, $name) => $q->where('name', 'ilike', "%{$name}%"))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(GuardianResource::collection($guardians)->response()->getData(true));
    }

    public function store(CreateGuardianRequest $request, CreateGuardianUseCase $useCase): JsonResponse
    {
        $guardian = $useCase->execute(new CreateGuardianDTO(
            name: $request->validated('name'),
            cpf: $request->validated('cpf'),
            rg: $request->validated('rg'),
            phone: $request->validated('phone'),
            phoneSecondary: $request->validated('phone_secondary'),
            email: $request->validated('email'),
            address: $request->validated('address'),
            occupation: $request->validated('occupation'),
        ));

        return $this->created(new GuardianResource($guardian));
    }

    public function show(int $id): JsonResponse
    {
        $guardian = Guardian::with('students')->findOrFail($id);

        return $this->success(new GuardianResource($guardian));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $guardian = Guardian::findOrFail($id);
        $guardian->update($request->only(['name', 'cpf', 'phone', 'phone_secondary', 'email', 'address', 'occupation']));

        return $this->success(new GuardianResource($guardian->refresh()));
    }
}
