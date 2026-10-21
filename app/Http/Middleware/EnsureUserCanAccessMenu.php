<?php

namespace App\Http\Middleware;

use App\Services\HakAksesService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessMenu
{
    /**
     * Pastikan user yang sedang login memiliki hak akses menu yang diminta.
     */
    public function handle(Request $request, Closure $next, ?string $menuKey = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $menuKey) {
            $menuKey = HakAksesService::resolveMenuKeyFromRoute($request->route()?->getName());
        }

        if ($menuKey && ! HakAksesService::canAccess($user, $menuKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk membuka fitur ini.',
                ], 403);
            }

            abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses untuk membuka menu ini.');
        }

        return $next($request);
    }
}
