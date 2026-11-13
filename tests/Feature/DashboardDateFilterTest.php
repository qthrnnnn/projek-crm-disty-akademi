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
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/dashboard?date=2026-10-07');

        $response
            ->assertOk()
            ->assertViewHas('selectedDateString', '2026-10-07')
            ->assertSee('Lead');
    }

    public function test_dashboard_rejects_invalid_selected_date(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/dashboard?date=07-10-2026');

        $response->assertSessionHasErrors('date');
    }
}
