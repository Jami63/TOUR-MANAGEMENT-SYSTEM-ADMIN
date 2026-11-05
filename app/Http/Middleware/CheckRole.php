<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! in_array($user->role, $roles)) {
            if ($user->isSuperAdmin()) {
                return redirect()->route('admin.dashboard')->with('error', 'Super Admin has strictly read-only monitoring access.');
            }

            if ($user->isManager()) {
                return redirect()->route('manager.dashboard')->with('error', 'Unauthorized access.');
            }

            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
