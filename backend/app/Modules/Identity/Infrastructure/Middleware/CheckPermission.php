<?php

namespace App\Modules\Identity\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return response()->json(['error' => 'Não autenticado.'], 401);
        }

        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        if (! $request->user()->hasPermission($permission)) {
            return response()->json(['error' => 'Sem permissão para esta ação.'], 403);
        }

        return $next($request);
    }
}
