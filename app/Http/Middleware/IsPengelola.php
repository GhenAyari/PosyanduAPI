<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsPengelola
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kalau role-nya BUKAN superadmin/kader, tolak akses
        if (!in_array($request->user()->role, ['superadmin', 'kader'])) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Akses ditolak. Hanya pengelola yang diperbolehkan.'
            ], 403);
        }

        return $next($request);
    }
}
