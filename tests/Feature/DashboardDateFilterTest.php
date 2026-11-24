<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDateFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dashboard_for_selected_date(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($user)->getJson('/api/dashboard?date=2026-10-07');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.selectedDateString', '2026-10-07');
    }

    public function test_dashboard_rejects_invalid_selected_date(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($user)->getJson('/api/dashboard?date=07-10-2026');

        $response->assertUnprocessable()->assertJsonValidationErrors('date');
    }
}
