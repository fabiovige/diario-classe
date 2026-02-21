<?php

namespace App\Modules\Identity\Presentation\Controllers;

use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Presentation\Requests\CreateRoleRequest;
use App\Modules\Identity\Presentation\Requests\UpdateRoleRequest;
use App\Modules\Identity\Presentation\Resources\RoleResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $roles = Role::query()
            ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(RoleResource::collection($roles)->response()->getData(true));
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        return $this->created(new RoleResource($role));
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        return $this->success(new RoleResource($role));
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->update($request->validated());

        return $this->success(new RoleResource($role->refresh()));
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return $this->noContent();
    }
}
