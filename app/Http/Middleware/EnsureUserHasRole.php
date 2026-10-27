<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Silakan login terlebih dahulu.');
        }

        if (! $user->is_active) {
            abort(Response::HTTP_FORBIDDEN, 'Akun Anda sedang dinonaktifkan.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $userRoleValue = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        // Superadmin has access to all protected dashboards
        if ($userRoleValue === UserRole::Superadmin->value) {
            return $next($request);
        }

        if (! in_array($userRoleValue, $roles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
