<?php

namespace App\Modules\Identity\Presentation\Controllers;

use App\Modules\Identity\Application\DTOs\LoginDTO;
use App\Modules\Identity\Application\UseCases\LoginUseCase;
use App\Modules\Identity\Application\UseCases\LogoutUseCase;
use App\Modules\Identity\Presentation\Requests\LoginRequest;
use App\Modules\Identity\Presentation\Resources\LoginResource;
use App\Modules\Identity\Presentation\Resources\UserResource;
use App\Modules\Shared\Presentation\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function login(LoginRequest $request, LoginUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(new LoginDTO(
            email: $request->validated('email'),
            password: $request->validated('password'),
        ));

        return $this->success(new LoginResource($result));
    }

    public function logout(Request $request, LogoutUseCase $useCase): JsonResponse
    {
        $useCase->execute($request->user());

        return $this->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()->load('role')));
    }
}
