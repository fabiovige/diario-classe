<?php

namespace App\Modules\Identity\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json(['error' => 'Não autenticado.'], 401);
        }

        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        if (! $request->user()->school_id) {
            return response()->json(['error' => 'Usuário sem escola vinculada.'], 403);
        }

        return $next($request);
    }
}
