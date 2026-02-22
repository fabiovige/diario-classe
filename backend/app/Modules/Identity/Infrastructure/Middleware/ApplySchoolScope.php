<?php

namespace App\Modules\Identity\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySchoolScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json(['error' => 'Nao autenticado.'], 401);
        }

        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        if (! $request->user()->school_id) {
            return response()->json(['error' => 'Usuario sem escola vinculada.'], 403);
        }

        $request->query->set('school_id', (string) $request->user()->school_id);

        return $next($request);
    }
}
