<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API, always return null to prevent redirects
        return null;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (in_array($request->route()->getName(), ['login'])) {
            return $next($request);
        }
    
        // Set JWT token from cookie if available
        if ($jwt = $request->cookie('jwt')) {
            $request->headers->set('Authorization', 'Bearer ' . $jwt);
        }

        try {
            // Try to authenticate the user
            $this->authenticate($request, $guards);
            return $next($request);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // For API, return JSON response instead of redirecting
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => false
            ], 401);
        }
    }
}