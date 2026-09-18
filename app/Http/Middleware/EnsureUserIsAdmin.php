<?php

namespace App\Http\Middleware;

use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessAdmin()) {
            abort(403, 'Access denied.');
        }

        try {
            $user->loadMissing(['role.permissions', 'permissions']);
        } catch (\Throwable) {
            // Roles tables may not exist yet on a freshly deployed server.
        }

        $resolved = AdminAccess::resolve($request->route()?->getName(), $request->method());

        if ($resolved === null) {
            return $next($request);
        }

        [$module, $action] = $resolved;

        if ($user->hasPermission($module, $action)) {
            return $next($request);
        }

        if ($request->routeIs('admin.dashboard')) {
            $fallback = AdminAccess::firstAccessibleRoute($user);

            if ($fallback && $fallback !== 'admin.dashboard') {
                return redirect()->route($fallback);
            }
        }

        abort(403, 'You do not have permission to access this module.');
    }
}
