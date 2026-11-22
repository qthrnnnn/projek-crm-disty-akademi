<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Institution;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Registration;
use App\Models\Trainer;
use App\Models\TrainingSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesCustomerVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_payment_visibility_follow_the_assigned_sales_after_reassignment(): void
    {
        $registrant = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $institution = Institution::create(['name' => 'Lembaga Pelatihan']);
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM']);
        $trainer = Trainer::create(['name' => 'Trainer CRM']);
        $schedule = TrainingSchedule::create([
            'product_id' => $product->id,
            'trainer_id' => $trainer->id,
            'training_date' => '2026-12-01',
            'start_time' => '09:00',
            'end_time' => '16:00',
        ]);
        $lead = Lead::create([
            'name' => 'Peserta Pelatihan',
            'lead_code' => 'LDA-PESERTA-01',
            'assigned_to' => $registrant->id,
        ]);
        $customer = Customer::create([
            'name' => $lead->name,
            'customer_code' => 'CUST-PESERTA-01',
            'lead_id' => $lead->id,
            'institution_id' => $institution->id,
        ]);
        $registration = Registration::create([
            'customer_id' => $customer->id,
            'user_id' => $registrant->id,
            'product_id' => $product->id,
            'training_schedule_id' => $schedule->id,
        ]);
        $lead->update(['assigned_to' => $owner->id]);

        $this->actingAs($registrant)
            ->getJson('/api/registrations?for_payment=1&status=registered')
            ->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/registrations')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/customers')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/customers/{$customer->id}")->assertNotFound();
        $this->getJson("/api/registrations/{$registration->id}")->assertNotFound();
        $this->getJson("/api/institutions/{$institution->id}")
            ->assertOk()->assertJsonCount(0, 'data.customers')->assertJsonPath('data.customers_count', 0);
        $this->getJson("/api/training-schedules/{$schedule->id}")
            ->assertOk()->assertJsonCount(0, 'data.registrations');

        $this->actingAs($owner)
            ->getJson('/api/v1/registrations?for_payment=1&status=registered')
            ->assertOk()->assertJsonPath('data.0.id', $registration->id);
        $this->getJson('/api/customers')->assertOk()->assertJsonPath('data.0.id', $customer->id);
        $this->getJson("/api/customers/{$customer->id}")
            ->assertOk()->assertJsonPath('data.registrations.0.id', $registration->id);

        $this->actingAs($admin)
            ->getJson("/api/registrations?sales_id={$owner->id}")
            ->assertOk()->assertJsonPath('data.0.id', $registration->id);
    }

    public function test_registration_creator_retains_visibility_when_the_customer_has_no_assigned_lead(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $otherSales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM']);
        $customer = Customer::create(['name' => 'Peserta Manual', 'customer_code' => 'CUST-PESERTA-02']);
        $registration = Registration::create([
            'customer_id' => $customer->id,
            'user_id' => $sales->id,
            'product_id' => $product->id,
        ]);
        Registration::create([
            'customer_id' => $customer->id,
            'user_id' => $otherSales->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($sales)
            ->getJson('/api/registrations')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $registration->id);
        $this->getJson("/api/customers/{$customer->id}")
            ->assertOk()->assertJsonCount(1, 'data.registrations')->assertJsonPath('data.registrations.0.id', $registration->id);
        $this->getJson('/api/registrations?for_payment=1&status=registered')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $registration->id);
    }
}
