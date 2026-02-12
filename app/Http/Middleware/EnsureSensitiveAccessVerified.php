<?php

namespace App\Http\Middleware;

use App\Infrastructure\Security\SensitiveAccess;
use Closure;
use Illuminate\Http\Request;

class EnsureSensitiveAccessVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!SensitiveAccess::isVerified($request)) {
            $request->session()->put('sensitive_access_intended', $request->fullUrl());

            return redirect()->route('security.sensitive-verify');
        }

        return $next($request);
    }
}
