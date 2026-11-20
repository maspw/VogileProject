<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Validasi role user yang sedang login terhadap relasi `users.id_role` -> `role`.
     * Contoh pemakaian: ->middleware('role:Owner,Admin Pembelian')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // Tanpa batasan role spesifik, teruskan request
        if (empty($roles)) {
            return $next($request);
        }

        // Owner dan super admin (U000) memiliki akses penuh lintas modul
        if ($user->hasRole('Owner') || $user->id_user === 'U000') {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->hasRole(trim($role))) {
                return $next($request);
            }
        }

        $roleName = $user->role?->nama_role ?? 'Tanpa Role';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Forbidden. Role Anda ({$roleName}) tidak memiliki izin mengakses modul ini.",
            ], 403);
        }

        abort(403, "Akses Ditolak: role {$roleName} tidak memiliki wewenang untuk mengakses halaman ini.");
    }
}
