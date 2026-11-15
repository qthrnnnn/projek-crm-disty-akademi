<?php

namespace App\Http\Middleware;

use App\Exceptions\TokenException;
use App\Models\User;
use App\Support\Jwt;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/* [MODUL 6] Middleware Autentikasi JWT HS256.
   Mengecek header Authorization: Bearer <token>.
   Bila valid, pasang instance User ke Auth & Request agar $request->user()
   dan middleware role seperti EnsureAdmin tetap berfungsi normal.
*/
class Autentikasi
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->header('Authorization', '');
        $parts = explode(' ', $header, 2);
        $scheme = $parts[0] ?? '';
        $token = $parts[1] ?? '';

        // 1. Jika request menyertakan Bearer token di header Authorization, prioritaskan verifikasi JWT
        if (strtolower($scheme) === 'bearer' && ! empty($token)) {
            $secret = config('jwt.rahasia', config('disty.jwt.rahasia', 'default-jwt-secret'));

            try {
                $payload = Jwt::periksa($token, $secret);
            } catch (TokenException $e) {
                $isExpired = $e->kode === 'KEDALUWARSA';

                return response()->json([
                    'success' => false,
                    'code' => $isExpired ? 'TOKEN_KEDALUWARSA' : 'TOKEN_TIDAK_VALID',
                    'message' => $isExpired
                        ? 'Sesi Anda telah berakhir, silakan login kembali.'
                        : 'Token otentikasi tidak valid.',
                ], 401);
            }

            $userId = $payload['sub'] ?? null;
            $user = User::find($userId);

            if (! $user || ! $user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun pengguna tidak ditemukan atau telah dinonaktifkan.',
                ], 401);
            }

            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);
            $request->attributes->set('jwt_payload', $payload);

            return $next($request);
        }

        // 2. Jika tidak ada Bearer token, fallback cek apakah sudah diautentikasi via session atau actingAs
        if (Auth::check() && Auth::user()?->is_active) {
            $user = Auth::user();
            $request->setUserResolver(fn () => $user);

            return $next($request);
        }

        // 3. Tanpa token dan tanpa autentikasi
        return response()->json([
            'success' => false,
            'message' => 'Silakan masuk terlebih dahulu (Token Bearer diperlukan).',
        ], 401);
    }
}
