<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\ImportBatch;
use App\Models\Institution;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Registration;
use App\Models\RejectionReason;
use App\Models\SalesReminder;
use App\Models\Trainer;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Support\Jwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sales;
    private string $adminToken;
    private string $salesToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@distyakademi.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->sales = User::factory()->create([
            'email' => 'sales@distyakademi.com',
            'password' => bcrypt('password123'),
            'role' => 'sales',
            'is_active' => true,
        ]);

        $secret = config('jwt.rahasia');
        $this->adminToken = Jwt::buat([
            'sub' => $this->admin->id,
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'admin',
        ], $secret, 3600);

        $this->salesToken = Jwt::buat([
            'sub' => $this->sales->id,
            'name' => $this->sales->name,
            'email' => $this->sales->email,
            'role' => 'sales',
        ], $secret, 3600);
    }

    public function test_01_public_endpoints(): void
    {
        // 1. CSRF Token
        $res = $this->getJson('/api/csrf-token');
        $res->assertOk()->assertJsonStructure(['token']);

        // 2. Leads template CSV
        $res = $this->get('/api/leads/template');
        $res->assertOk();

        // 3. Leads template Excel
        $res = $this->get('/api/leads/template-excel');
        $res->assertOk();
    }

    public function test_02_authentication_lifecycle(): void
    {
        // 1. Login success
        $res = $this->postJson('/api/login', [
            'email' => 'sales@distyakademi.com',
            'password' => 'password123',
        ]);
        $res->assertOk()
            ->assertJsonStructure(['token', 'tokenType', 'expiresIn', 'user']);

        // 2. Login invalid password
        $res = $this->postJson('/api/login', [
            'email' => 'sales@distyakademi.com',
            'password' => 'wrong',
        ]);
        $res->assertStatus(422);

        // 3. Get profile with Bearer token
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->getJson('/api/user');
        $res->assertOk()->assertJsonPath('data.email', 'sales@distyakademi.com');

        // 4. Update bank account
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->postJson('/api/user/bank-account', [
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'Budi Santoso',
            ]);
        $res->assertOk()
            ->assertJsonPath('data.bank_name', 'BCA');

        // 5. Logout
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->postJson('/api/logout');
        $res->assertOk()->assertJson(['success' => true]);
    }

    public function test_03_dashboard_api(): void
    {
        $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->getJson('/api/dashboard');
        $res->assertOk()
            ->assertJsonStructure(['success', 'data']);

        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->getJson('/api/dashboard?date=2026-11-15');
        $res->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_04_leads_full_workflow(): void
    {
        // 1. Create Lead
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->postJson('/api/leads', [
                'name' => 'Ahmad Fauzi',
                'phone' => '081234567899',
                'company' => 'PT Maju Terus',
            ]);
        $res->assertStatus(201);
        $leadId = $res->json('data.id');

        // 2. List Leads
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->getJson('/api/leads?search=Ahmad');
        $res->assertOk()->assertJsonStructure(['data']);

        // 3. Show Lead
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->getJson("/api/leads/{$leadId}");
        $res->assertOk()->assertJsonPath('data.name', 'Ahmad Fauzi');

        // 4. Update Status
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->patchJson("/api/leads/{$leadId}/status", [
                'status' => 'contacted',
            ]);
        $res->assertOk();

        // 5. Add Activity
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->postJson("/api/leads/{$leadId}/activities", [
                'type' => 'call',
                'subject' => 'Telepon perkenalan',
                'description' => 'Calon peserta tertarik paket data science',
                'activity_at' => now()->toDateTimeString(),
            ]);
        $res->assertOk();

        // 6. Claim Lead
        $unassignedLead = Lead::create([
            'lead_code' => 'LDA-UNASSIGNED',
            'name' => 'Unassigned Lead',
            'phone' => '0899999999',
            'status' => 'new',
            'assigned_to' => null,
        ]);
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->postJson("/api/leads/{$unassignedLead->id}/claim");
        $res->assertOk();
        $this->assertEquals($this->sales->id, $unassignedLead->fresh()->assigned_to);
    }

    public function test_05_reminders_and_follow_ups(): void
    {
        $lead = Lead::create([
            'lead_code' => 'LDA-REMIND-01',
            'name' => 'Lead For Reminder',
            'phone' => '0877777777',
            'status' => 'new',
            'assigned_to' => $this->sales->id,
        ]);

        // 1. Admin creates reminder for Sales
        $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson("/api/leads/{$lead->id}/remind-sales", [
                'sales_id' => $this->sales->id,
                'message' => 'Tolong follow up segera via telepon!',
                'priority' => 'urgent',
            ]);
        $res->assertOk();

        // 2. Sales views reminders
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->getJson('/api/reminders');
        $res->assertOk()->assertJsonStructure(['data']);
        $reminderId = SalesReminder::where('sales_id', $this->sales->id)->first()->id;

        // 3. Mark read
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->patchJson("/api/reminders/{$reminderId}/read");
        $res->assertOk();

        // 4. Mark all read
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->postJson('/api/reminders/mark-all-read');
        $res->assertOk();

        // 5. Complete reminder
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->patchJson("/api/reminders/{$reminderId}/complete", [
                'reply' => 'Sudah ditelepon dan siap closing!',
            ]);
        $res->assertOk();

        // 6. Follow-up scheduling
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->postJson('/api/follow-ups', [
                'lead_id' => $lead->id,
                'title' => 'Follow Up Telepon Lanjutan',
                'method' => 'call',
                'follow_up_at' => now()->addDay()->toDateTimeString(),
            ]);
        $res->assertStatus(201);
        $fuId = $res->json('data.id');

        // 7. Complete Follow Up
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->patchJson("/api/follow-ups/{$fuId}/complete", [
                'result_notes' => 'Customer konfirmasi ambil sertifikasi',
            ]);
        $res->assertOk();

        // 8. Cancel Follow Up
        $fu2 = FollowUp::create([
            'lead_id' => $lead->id,
            'title' => 'Follow Up 2',
            'method' => 'whatsapp',
            'status' => 'pending',
            'follow_up_at' => now()->addDays(2),
            'assigned_to' => $this->sales->id,
        ]);
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->patchJson("/api/follow-ups/{$fu2->id}/cancel");
        $res->assertOk();
    }

    public function test_06_payments_and_commissions(): void
    {
        $product = Product::create([
            'name' => 'Data Science Specialist',
            'code' => 'DSS-01',
            'price' => 2000000,
            'is_active' => true,
        ]);

        $lead = Lead::create([
            'lead_code' => 'LDA-PAY-01',
            'name' => 'Peserta Bayar',
            'phone' => '0855555555',
            'status' => 'negotiation',
            'assigned_to' => $this->sales->id,
            'product_id' => $product->id,
            'deal_price' => 2000000,
        ]);

        // 1. Sales records payment
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->postJson('/api/payments', [
                'lead_id' => $lead->id,
                'amount' => 2000000,
                'payment_method' => 'bank_transfer',
                'payment_date' => now()->toDateString(),
            ]);
        $this->assertTrue(in_array($res->status(), [200, 201]));
        $payment = Payment::where('lead_id', $lead->id)->first();
        $this->assertNotNull($payment);

        // 2. Admin verifies payment as VALID
        $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->patchJson("/api/payments/{$payment->id}/verify", [
                'status' => 'valid',
                'verification_notes' => 'Dana sudah masuk rekening koran',
            ]);
        $res->assertOk();

        // Lead should automatically be 'won'
        $this->assertEquals('won', $lead->fresh()->status);

        // Commission should be created automatically
        $comm = Commission::where('lead_id', $lead->id)->first();
        $this->assertNotNull($comm);

        // 3. Admin approves commission
        $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->patchJson("/api/commissions/{$comm->id}/approve");
        $res->assertOk();

        // 4. Admin pays / marks commission paid
        $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->patchJson("/api/commissions/{$comm->id}/pay", [
                'notes' => 'Lunas transfer via payroll',
                'transfer_receipt' => 'TRX-99887766',
            ]);
        $res->assertOk();
        $this->assertEquals('paid', $comm->fresh()->status);
    }

    public function test_07_customers_and_export(): void
    {
        // 1. Create customer
        $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/api/customers', [
                'name' => 'Alumni Hebat',
                'email' => 'alumni@example.com',
                'phone' => '0833333333',
                'status' => 'active',
            ]);
        $res->assertStatus(201);
        $custId = $res->json('data.id');

        // 2. List customers
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->getJson('/api/customers');
        $res->assertOk();

        // 3. Show customer
        $res = $this->withHeader('Authorization', 'Bearer '.$this->salesToken)
            ->getJson("/api/customers/{$custId}");
        $res->assertOk()->assertJsonPath('data.name', 'Alumni Hebat');

        // 4. Export CSV (Admin only)
        $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->get('/api/customers/export');
        $res->assertOk();

        // 5. Customer template
        $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->get('/api/customers/template');
        $res->assertOk();
    }

    public function test_08_all_16_resources_crud_readable(): void
    {
        $resources = [
            'activities',
            'commissions',
            'customers',
            'follow-ups',
            'institutions',
            'import-batches',
            'lead-sources',
            'leads',
            'payments',
            'products',
            'rejection-reasons',
            'registrations',
            'reminders',
            'trainers',
            'training-schedules',
            'users',
        ];

        foreach ($resources as $resource) {
            $res = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
                ->getJson("/api/{$resource}");

            $this->assertTrue(
                $res->isOk(),
                "Resource endpoint GET /api/{$resource} failed with status {$res->status()}: {$res->getContent()}"
            );
        }
    }
}
