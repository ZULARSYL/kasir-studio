<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $userRole = $user?->role;

        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $trimmed = trim($subRole);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        // Cek jika user memiliki izin menu spesifik yang sedang diakses pada area admin (misal kasir/jabatan diberikan akses laporan admin)
        $routeName = $request->route()?->getName();
        if ($user && $routeName && str_starts_with($routeName, 'admin.') && ! str_starts_with($routeName, 'admin.kasir.')) {
            $menuKey = \App\Services\HakAksesService::resolveMenuKeyFromRoute($routeName);
            if ($menuKey && \App\Services\HakAksesService::canAccess($user, $menuKey)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
