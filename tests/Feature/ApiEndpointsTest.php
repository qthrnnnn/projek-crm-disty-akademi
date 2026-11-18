<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Support\Jwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_login_successful_and_returns_jwt_token(): void
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
                'token',
                'tokenType',
                'expiresIn',
                'user' => ['id', 'name', 'email', 'role'],
            ]);

        $token = $response->json('token');
        $this->assertNotEmpty($token);

        // Test mengakses API terproteksi menggunakan JWT Bearer token
        $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $meResponse->assertOk()
            ->assertJsonPath('data.email', 'sales@distyakademi.com');
    }

    public function test_auth_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'sales@distyakademi.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'sales@distyakademi.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_get_profile_and_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        // Profile
        $profileResponse = $this->actingAs($user)
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
        $dashboardResponse = $this->actingAs($user)
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
        // Create Lead
        $storeResponse = $this->actingAs($sales)
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
        $showResponse = $this->actingAs($sales)
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
        $statusResponse = $this->actingAs($sales)
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
        // Store Product as Admin
        $prodResponse = $this->actingAs($admin)
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
        $custResponse = $this->actingAs($admin)
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

    public function test_protected_api_rejects_missing_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_logout_endpoint_succeeds(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['success' => true]);
    }
}
