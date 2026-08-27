<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isAdministrator()) {
            return response()->json([
                'message' => 'Apenas o administrador da oficina pode alterar estas configurações.',
            ], 403);
        }

        return $next($request);
    }
}
