<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StoreUserRoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            session(['is_super_admin' => auth()->user()->hasRole('super_admin')]);
        }

        return $next($request);
    }
}
