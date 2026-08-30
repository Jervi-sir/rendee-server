<?php

namespace App\Http\Middleware;

use App\Models\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $roleCode = $user->user_role_code ?? $user->userRole?->code;

        if ($roleCode !== UserRole::ADMIN && $roleCode !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
