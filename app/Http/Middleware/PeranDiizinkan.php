<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/* [MODUL 6] MIDDLEWARE OTORISASI PERAN (padanan PeranDiizinkan di HelpDesk).
   "Boleh?" -> 403 bila sudah login tetapi perannya tidak diizinkan.
   Pemakaian di rute: ->middleware('peran:Admin') atau ->middleware('peran:Admin,Sales')
   Harus dipasang SETELAH middleware 'jwt'.
*/
class PeranDiizinkan
{
    public function handle(Request $request, Closure $next, string ...$diizinkan): Response
    {
        $pengguna = $request->attributes->get('pengguna');
        $peran = $pengguna['role']
            ?? $request->user()?->role
            ?? ($request->attributes->get('jwt_payload')['role'] ?? null);

        // Parsing peran yang diizinkan (mendukung 'peran:Admin,Sales' dan 'peran:admin')
        $roles = [];
        foreach ($diizinkan as $item) {
            foreach (explode(',', $item) as $r) {
                $trimmed = trim(strtolower($r));
                if ($trimmed !== '') {
                    $roles[] = $trimmed;
                }
            }
        }

        if (! in_array(strtolower((string) $peran), $roles, true)) {
            return response()->json([
                'success' => false,
                'code' => 'DILARANG',
                'message' => 'Kamu tidak punya akses untuk tindakan ini (Akses Ditolak).',
                'error' => [
                    'code' => 'DILARANG',
                    'message' => 'Kamu tidak punya akses untuk tindakan ini',
                ],
            ], 403);
        }

        return $next($request);
    }
}
