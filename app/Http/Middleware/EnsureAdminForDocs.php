<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminForDocs
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {

        $quard = auth()->guard('web');
        $user = $quard->user();

        if (!$user || !method_exists($user, 'hasAnyRole') || !$user->hasAnyRole(['super_admin'])) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
