<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Menerima parameter role, e.g. role:Owner,Sales,Admin Penjualan
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // Jika tidak ada batasan role spesifik, teruskan
        if (empty($roles)) {
            return $next($request);
        }

        // Super Admin selalu memiliki akses bypass
        if ($user->hasRole('Owner') || $user->id_user === 'U000') {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->hasRole(trim($role))) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Forbidden. Role Anda (' . ($user->role->nama_role ?? 'Tanpa Role') . ') tidak memiliki izin mengakses modul ini.'
            ], 403);
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses halaman ini.');
    }
}
