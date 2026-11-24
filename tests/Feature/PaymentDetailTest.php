<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_detail_includes_the_assigned_sales_for_the_pic_display(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['name' => 'Budi Santoso', 'role' => 'sales', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LDA-PIC',
            'name' => 'Peserta',
            'assigned_to' => $sales->id,
        ]);
        $payment = Payment::create(['lead_id' => $lead->id, 'amount' => 150000]);

        foreach (['/api', '/api/v1'] as $prefix) {
            $this->actingAs($admin)->getJson("{$prefix}/payments/{$payment->id}")
                ->assertOk()
                ->assertJsonPath('data.lead.assigned_user.id', $sales->id)
                ->assertJsonPath('data.lead.assigned_user.name', 'Budi Santoso')
                ->assertJsonMissingPath('data.lead.assigned_user.password');
        }
    }

    public function test_payment_detail_keeps_an_unassigned_pic_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $lead = Lead::create(['lead_code' => 'LDA-NO-PIC', 'name' => 'Peserta']);
        $payment = Payment::create(['lead_id' => $lead->id, 'amount' => 150000]);

        $this->actingAs($admin)->getJson("/api/payments/{$payment->id}")
            ->assertOk()
            ->assertJsonPath('data.lead.assigned_user', null);
    }
}
