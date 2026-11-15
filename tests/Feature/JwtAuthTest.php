<?php

namespace Tests\Feature;

use App\Exceptions\TokenException;
use App\Models\User;
use App\Support\Jwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_jwt_helper_creates_and_verifies_valid_token(): void
    {
        $payload = ['sub' => 123, 'name' => 'Budi', 'role' => 'sales'];
        $secret = 'kunci-rahasia-test';
        $ttl = 3600;

        $token = Jwt::buat($payload, $secret, $ttl);
        $this->assertNotEmpty($token);
        $this->assertCount(3, explode('.', $token));

        $decoded = Jwt::periksa($token, $secret);
        $this->assertEquals(123, $decoded['sub']);
        $this->assertEquals('Budi', $decoded['name']);
        $this->assertEquals('sales', $decoded['role']);
        $this->assertArrayHasKey('iat', $decoded);
        $this->assertArrayHasKey('exp', $decoded);
    }

    public function test_jwt_helper_rejects_tampered_token(): void
    {
        $payload = ['sub' => 123, 'name' => 'Budi'];
        $secret = 'kunci-rahasia-test';
        $token = Jwt::buat($payload, $secret, 3600);

        $parts = explode('.', $token);
        $tamperedPayload = rtrim(strtr(base64_encode(json_encode(['sub' => 999, 'name' => 'Hacker'])), '+/', '-_'), '=');
        $tamperedToken = "{$parts[0]}.{$tamperedPayload}.{$parts[2]}";

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage('Tanda tangan token tidak cocok');

        Jwt::periksa($tamperedToken, $secret);
    }

    public function test_jwt_helper_rejects_expired_token(): void
    {
        $payload = ['sub' => 123];
        $secret = 'kunci-rahasia-test';

        $token = Jwt::buat($payload, $secret, -10);

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage('Token sudah kedaluwarsa');

        Jwt::periksa($token, $secret);
    }

    public function test_login_returns_jwt_token(): void
    {
        $user = User::factory()->create([
            'email' => 'sales@distyakademi.com',
            'password' => bcrypt('password123'),
            'role' => 'sales',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'sales@distyakademi.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'token',
                'tokenType',
                'expiresIn',
                'user' => ['id', 'name', 'email', 'role'],
            ])
            ->assertJson([
                'success' => true,
                'tokenType' => 'Bearer',
            ]);

        $token = $response->json('token');
        $this->assertNotEmpty($token);

        // Verify the payload inside the token
        $secret = config('jwt.rahasia');
        $payload = Jwt::periksa($token, $secret);
        $this->assertEquals($user->id, $payload['sub']);
        $this->assertEquals('sales', $payload['role']);
    }

    public function test_protected_routes_work_with_bearer_jwt(): void
    {
        $user = User::factory()->create([
            'role' => 'sales',
            'is_active' => true,
        ]);

        $secret = config('jwt.rahasia');
        $token = Jwt::buat([
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], $secret, 3600);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_protected_routes_reject_invalid_bearer_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid.token.value')
            ->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_protected_routes_reject_expired_bearer_token(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $secret = config('jwt.rahasia');
        $expiredToken = Jwt::buat(['sub' => $user->id], $secret, -100);

        $response = $this->withHeader('Authorization', 'Bearer '.$expiredToken)
            ->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'TOKEN_KEDALUWARSA',
            ]);
    }

    public function test_protected_routes_reject_missing_token(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_protected_routes_reject_inactive_user_token(): void
    {
        $user = User::factory()->create([
            'role' => 'sales',
            'is_active' => false,
        ]);

        $secret = config('jwt.rahasia');
        $token = Jwt::buat(['sub' => $user->id], $secret, 3600);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Akun pengguna tidak ditemukan atau telah dinonaktifkan.',
            ]);
    }
}
