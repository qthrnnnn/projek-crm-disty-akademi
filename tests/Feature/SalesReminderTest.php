<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Lead;
use App\Models\SalesReminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_reminder_to_sales(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LEAD-TEST-001',
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'whatsapp' => '081234567890',
            'status' => 'new',
            'assigned_to' => $sales->id,
        ]);

        $response = $this->actingAs($admin)->postJson(route('leads.remind-sales', $lead), [
            'sales_id' => $sales->id,
            'message' => 'Tolong segera chat customer via WA ya!',
            'priority' => 'urgent',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('sales_reminders', [
            'lead_id' => $lead->id,
            'admin_id' => $admin->id,
            'sales_id' => $sales->id,
            'message' => 'Tolong segera chat customer via WA ya!',
            'priority' => 'urgent',
            'is_read' => false,
            'is_completed' => false,
        ]);

        // Verify activity logged
        $this->assertDatabaseHas('activities', [
            'lead_id' => $lead->id,
            'type' => 'admin_reminder',
            'user_id' => $admin->id,
        ]);
    }

    public function test_sales_cannot_send_reminder(): void
    {
        $sales1 = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $sales2 = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LEAD-TEST-002',
            'name' => 'Ahmad Dani',
            'status' => 'new',
        ]);

        $response = $this->actingAs($sales1)->postJson(route('leads.remind-sales', $lead), [
            'sales_id' => $sales2->id,
            'message' => 'Chat prospek ini dong',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('sales_reminders', [
            'lead_id' => $lead->id,
        ]);
    }

    public function test_sales_can_mark_reminder_completed(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LEAD-TEST-003',
            'name' => 'Siti Nurhaliza',
            'phone' => '08987654321',
            'status' => 'new',
            'assigned_to' => $sales->id,
        ]);

        $reminder = SalesReminder::create([
            'lead_id' => $lead->id,
            'admin_id' => $admin->id,
            'sales_id' => $sales->id,
            'message' => 'Hubungi via WA sekarang',
            'priority' => 'urgent',
            'is_read' => false,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($sales)->patchJson(route('reminders.complete', $reminder), [
            'sales_reply' => 'Sudah di-chat, prospek minta silabus materi.',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('sales_reminders', [
            'id' => $reminder->id,
            'is_completed' => true,
            'sales_reply' => 'Sudah di-chat, prospek minta silabus materi.',
        ]);

        // Lead activity recorded
        $this->assertDatabaseHas('activities', [
            'lead_id' => $lead->id,
            'type' => 'whatsapp',
            'user_id' => $sales->id,
        ]);
    }

    public function test_admin_creating_followup_schedule_automatically_notifies_sales(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LEAD-TEST-004',
            'name' => 'Jongu UPN',
            'phone' => '085812304685',
            'whatsapp' => '085812304685',
            'status' => 'new',
            'assigned_to' => $sales->id,
        ]);

        $response = $this->actingAs($admin)->postJson(route('follow-ups.store'), [
            'lead_id' => $lead->id,
            'title' => 'konfirmasi bukti tf',
            'description' => 'Tolong tanyakan bukti transfer ke customer',
            'follow_up_at' => now()->addHour()->toDateTimeString(),
            'method' => 'whatsapp',
            'priority' => 'urgent',
            'assigned_to' => $sales->id,
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        // Verify reminder created automatically for Sales
        $this->assertDatabaseHas('sales_reminders', [
            'lead_id' => $lead->id,
            'sales_id' => $sales->id,
            'priority' => 'urgent',
            'is_completed' => false,
        ]);
    }

    public function test_sales_can_mark_reminder_completed_without_reply(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LEAD-TEST-005',
            'name' => 'Doni',
            'phone' => '08987654322',
            'status' => 'new',
            'assigned_to' => $sales->id,
        ]);

        $reminder = SalesReminder::create([
            'lead_id' => $lead->id,
            'admin_id' => $admin->id,
            'sales_id' => $sales->id,
            'message' => 'Chat WA ya',
            'priority' => 'urgent',
            'is_read' => false,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($sales)->patchJson(route('reminders.complete', $reminder), []);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('sales_reminders', [
            'id' => $reminder->id,
            'is_completed' => true,
        ]);
    }

    public function test_marking_commission_paid_creates_notification_for_sales(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LEAD-TEST-006',
            'name' => 'Dimas Pratama',
            'phone' => '081299887766',
            'status' => 'won',
            'assigned_to' => $sales->id,
        ]);

        $commission = Commission::create([
            'user_id' => $sales->id,
            'lead_id' => $lead->id,
            'deal_amount' => 1500000,
            'commission_rate' => 10.00,
            'commission_amount' => 150000,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->patchJson(route('commissions.pay', $commission));
        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('sales_reminders', [
            'type' => 'commission_paid',
            'commission_id' => $commission->id,
            'sales_id' => $sales->id,
            'is_completed' => true,
        ]);
    }

    public function test_sales_opening_reminders_or_calling_mark_all_read_marks_notifications_read(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LEAD-TEST-007',
            'name' => 'Rina',
            'status' => 'new',
            'assigned_to' => $sales->id,
        ]);

        $reminder = SalesReminder::create([
            'lead_id' => $lead->id,
            'admin_id' => $admin->id,
            'sales_id' => $sales->id,
            'message' => 'Chat WA ya',
            'is_read' => false,
        ]);

        $response = $this->actingAs($sales)->postJson(route('reminders.mark-all-read'));
        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales_reminders', [
            'id' => $reminder->id,
            'is_read' => true,
        ]);
    }

    public function test_commission_notification_displays_proper_commission_badge_and_not_follow_up_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create([
            'lead_code' => 'LEAD-TEST-008',
            'name' => 'Dimas Pratama',
            'status' => 'won',
            'assigned_to' => $sales->id,
        ]);

        $commission = Commission::create([
            'user_id' => $sales->id,
            'lead_id' => $lead->id,
            'deal_amount' => 1500000,
            'commission_rate' => 10.00,
            'commission_amount' => 150000,
            'status' => 'paid',
        ]);

        SalesReminder::create([
            'type' => 'commission_paid',
            'lead_id' => $lead->id,
            'admin_id' => $admin->id,
            'commission_id' => $commission->id,
            'sales_id' => $sales->id,
            'message' => 'Selamat! Komisi 10% sebesar Rp 150.000 untuk prospek Dimas Pratama telah berhasil dibayarkan.',
            'is_completed' => true,
            'is_read' => false,
        ]);

        $response = $this->actingAs($sales)->getJson(route('reminders.index'));
        $response->assertOk()
            ->assertJsonFragment([
                'type' => 'commission_paid',
                'commission_id' => $commission->id,
                'is_completed' => true,
            ]);
        $this->assertStringContainsString('Rp 150.000', json_encode($response->json('data')));
    }
}
