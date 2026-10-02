<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictApiToMobileDomain
{
    /**
     * Allowed hosts for API requests.
     */
    protected const ALLOWED_HOSTS = [
        'for-mobile.rendee.app',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->header('host', $request->getHost());
        $host = explode(':', (string) $host)[0];

        // Allow local dev/testing on any host or LAN IP
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        if (! in_array($host, self::ALLOWED_HOSTS, true)) {
            return response()->json([
                'message' => 'Not Found',
            ], 404);
        }

        return $next($request);
    }
}
