<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Fitur operasional ini hanya dapat diakses oleh Admin.'], 403);
            }

            return redirect('/')->with('error', 'Akses dibatasi. Fitur ini khusus operasional Admin/Manajemen, bukan untuk sales/affiliate.');
        }

        return $next($request);
    }
}
