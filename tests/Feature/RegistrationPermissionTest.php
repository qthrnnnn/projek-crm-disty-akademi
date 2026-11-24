<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_register_any_participant_on_either_api_prefix(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $product = Product::create(['name' => 'Pelatihan', 'code' => 'PERMISSION']);
        $lead = Lead::create(['lead_code' => 'LDA-PERMISSION', 'name' => 'Calon Peserta', 'status' => 'new']);
        $customer = Customer::create(['customer_code' => 'CUST-PERMISSION', 'name' => 'Customer']);
        $this->actingAs($admin);

        foreach (['/api', '/api/v1'] as $prefix) {
            foreach ([
                ['lead_id' => $lead->id],
                ['customer_id' => $customer->id],
                ['participant_mode' => 'manual', 'participant_name' => 'Peserta Baru'],
            ] as $participant) {
                $this->postJson("{$prefix}/registrations", $participant + ['product_id' => $product->id])
                    ->assertForbidden();
            }
        }

        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseCount('customers', 1);
        $this->assertSame('new', $lead->fresh()->status);
    }

    public function test_sales_can_register_and_admin_can_still_view_and_edit(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $product = Product::create(['name' => 'Pelatihan', 'code' => 'PERMISSION']);

        foreach (['/api', '/api/v1'] as $prefix) {
            $id = $this->actingAs($sales)->postJson("{$prefix}/registrations", [
                'participant_mode' => 'manual',
                'participant_name' => 'Peserta Sales',
                'product_id' => $product->id,
            ])->assertCreated()->assertJsonPath('data.user_id', $sales->id)->json('data.id');

            $this->actingAs($admin)->getJson("{$prefix}/registrations/{$id}")->assertOk();
            $this->patchJson("{$prefix}/registrations/{$id}", ['notes' => 'Catatan admin'])
                ->assertOk()->assertJsonPath('data.notes', 'Catatan admin');
        }

        $this->assertDatabaseCount('registrations', 2);
    }
}
