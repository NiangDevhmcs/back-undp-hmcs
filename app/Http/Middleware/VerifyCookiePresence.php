<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyCookiePresence
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasCookie('XSRF-TOKEN') || !$request->hasCookie('laravel_session')) {
            return response()->json(['message' => 'Session expired'], 401);
        }
        return $next($request);
    }
}
