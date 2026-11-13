<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends BaseApiController
{
    /**
     * Login API untuk Web Client / Frontend
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->sendError('Kredensial email atau password salah.', 401);
        }

        if (! $user->is_active) {
            return $this->sendError('Akun Anda dinonaktifkan. Silakan hubungi Administrator.', 403);
        }

        $deviceName = $validated['device_name'] ?? 'web-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->sendSuccess([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
            ],
        ], 'Login berhasil.');
    }

    /**
     * Dapatkan data profil user yang sedang login
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sendSuccess([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'unread_reminders_count' => $user->unreadRemindersCount(),
        ], 'Data profil berhasil diambil.');
    }

    /**
     * Logout API (Mencabut token aktif)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->sendSuccess(null, 'Logout berhasil, token telah dicabut.');
    }
}
