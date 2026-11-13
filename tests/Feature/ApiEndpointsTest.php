<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_login_successful_and_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'sales@distyakademi.com',
            'password' => bcrypt('password123'),
            'role' => 'sales',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('api.auth.login'), [
            'email' => 'sales@distyakademi.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);
    }

    public function test_auth_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'sales@distyakademi.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson(route('api.auth.login'), [
            'email' => 'sales@distyakademi.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Kredensial email atau password salah.',
            ]);
    }

    public function test_authenticated_user_can_get_profile_and_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        // Profile
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.auth.me'));

        $profileResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => 'sales',
                ],
            ]);

        // Dashboard
        $dashboardResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.dashboard.index'));

        $dashboardResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary',
                    'leads_by_status',
                ],
            ]);
    }

    public function test_leads_crud_via_api(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $token = $sales->createToken('test')->plainTextToken;

        // Create Lead
        $storeResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.leads.store'), [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'whatsapp' => '081234567890',
                'company' => 'PT Maju Bersama',
            ]);

        $storeResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Budi Santoso',
                    'assigned_to' => $sales->id,
                    'status' => 'new',
                ],
            ]);

        $leadId = $storeResponse->json('data.id');

        // Show Lead
        $showResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.leads.show', $leadId));

        $showResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $leadId,
                    'name' => 'Budi Santoso',
                ],
            ]);

        // Update Status
        $statusResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson(route('api.leads.update-status', $leadId), [
                'status' => 'contacted',
            ]);

        $statusResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'contacted',
                ],
            ]);

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'status' => 'contacted',
        ]);
    }

    public function test_customer_and_product_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $token = $admin->createToken('test')->plainTextToken;

        // Store Product as Admin
        $prodResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.products.store'), [
                'name' => 'Data Science Certification',
                'code' => 'DSC-2026',
                'product_type' => 'certification',
                'price' => 3500000,
            ]);

        $prodResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'code' => 'DSC-2026',
                ],
            ]);

        // Store Customer
        $custResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customers.store'), [
                'name' => 'Andi Wijaya',
                'email' => 'andi@example.com',
                'phone' => '08987654321',
                'status' => 'active',
            ]);

        $custResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Andi Wijaya',
                ],
            ]);
    }
}
