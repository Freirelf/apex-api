<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure;
use Exception;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            abort(response()->json(['error' => 'No authenticated. Token absent or invalid'], 401));
        }

        return null; 
    }

    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (Exception $e) {
            return response()->json(['error' => 'No authenticated. Please check your access token.'], 401);
        }

        return $next($request);
    }
}
