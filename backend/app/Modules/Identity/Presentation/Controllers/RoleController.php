<?php

namespace App\Modules\Identity\Presentation\Controllers;

use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Presentation\Requests\CreateRoleRequest;
use App\Modules\Identity\Presentation\Requests\UpdateRoleRequest;
use App\Modules\Identity\Presentation\Resources\RoleResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class RoleController extends ApiController
{
    public function index(): JsonResponse
    {
        $roles = Role::orderBy('name')->get();

        return $this->success(RoleResource::collection($roles));
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
