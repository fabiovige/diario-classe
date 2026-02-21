<?php

namespace App\Modules\Identity\Presentation\Controllers;

use App\Models\User;
use App\Modules\Identity\Application\DTOs\CreateUserDTO;
use App\Modules\Identity\Application\DTOs\UpdateUserDTO;
use App\Modules\Identity\Application\UseCases\CreateUserUseCase;
use App\Modules\Identity\Application\UseCases\DeleteUserUseCase;
use App\Modules\Identity\Application\UseCases\UpdateUserUseCase;
use App\Modules\Identity\Presentation\Requests\CreateUserRequest;
use App\Modules\Identity\Presentation\Requests\UpdateUserRequest;
use App\Modules\Identity\Presentation\Resources\UserResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with('role')
            ->when($request->query('search'), fn ($q, $search) => $q->where(fn ($sub) => $sub->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->when($request->query('school_id'), fn ($q, $schoolId) => $q->where('school_id', $schoolId))
            ->when($request->query('role_id'), fn ($q, $roleId) => $q->where('role_id', $roleId))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(UserResource::collection($users)->response()->getData(true));
    }

    public function store(CreateUserRequest $request, CreateUserUseCase $useCase): JsonResponse
    {
        $user = $useCase->execute(new CreateUserDTO(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            cpf: $request->validated('cpf'),
            roleId: $request->validated('role_id'),
            schoolId: $request->validated('school_id'),
        ));

        return $this->created(new UserResource($user->load('role')));
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with('role')->findOrFail($id);

        return $this->success(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, int $id, UpdateUserUseCase $useCase): JsonResponse
    {
        $user = $useCase->execute(new UpdateUserDTO(
            id: $id,
            name: $request->validated('name'),
            email: $request->validated('email'),
            cpf: $request->validated('cpf'),
            status: $request->validated('status'),
            roleId: $request->validated('role_id'),
            schoolId: $request->validated('school_id'),
        ));

        return $this->success(new UserResource($user->load('role')));
    }

    public function destroy(int $id, DeleteUserUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return $this->noContent();
    }
}
