<?php

namespace Tests\Feature;

use App\Http\Middleware\AtomicCrmChanges;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Institution;
use App\Models\Lead;
use App\Models\Product;
use App\Models\SalesReminder;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ActivityAuditTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('resources')]
    public function test_crm_create_update_delete_are_recorded_with_actor_and_changes(string $resource): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $product = Product::create(['name' => 'Program', 'code' => 'AUDIT', 'price' => 100000]);
        $trainer = Trainer::create(['name' => 'Trainer']);
        $lead = Lead::create(['name' => 'Prospek', 'lead_code' => 'AUDIT-LEAD', 'assigned_to' => $sales->id, 'product_id' => $product->id]);
        $customer = Customer::create(['name' => 'Peserta', 'customer_code' => 'AUDIT-CUST', 'lead_id' => $lead->id]);
        $this->actingAs($admin);

        $payload = match ($resource) {
            'leads' => ['name' => 'Lead baru', 'phone' => '0812345'],
            'customers' => ['name' => 'Customer baru'],
            'institutions' => ['name' => 'Institut Baru'],
            'trainers' => ['name' => 'Trainer baru'],
            'products' => ['name' => 'Program baru', 'code' => 'AUDIT-NEW', 'price' => 100000],
            'lead-sources', 'rejection-reasons' => ['name' => 'audit', 'label' => 'Audit'],
            'users' => ['name' => 'Pengguna baru', 'email' => 'audit@example.test', 'password' => 'Secret123!', 'role' => 'sales'],
            'training-schedules' => ['product_id' => $product->id, 'trainer_id' => $trainer->id, 'training_date' => '2026-12-01', 'start_time' => '09:00', 'end_time' => '16:00', 'location' => 'Zoom Meeting'],
            'registrations' => ['customer_id' => $customer->id, 'product_id' => $product->id, 'amount' => 100000],
            'payments' => ['lead_id' => $lead->id, 'product_id' => $product->id, 'amount' => 100000, 'notes' => 'Awal'],
            'commissions' => ['user_id' => $sales->id, 'lead_id' => $lead->id, 'deal_amount' => 100000],
            'follow-ups' => ['lead_id' => $lead->id, 'assigned_to' => $sales->id, 'title' => 'Follow-up baru'],
            'reminders' => ['lead_id' => $lead->id, 'sales_id' => $sales->id, 'admin_id' => $admin->id, 'message' => 'Pengingat baru'],
            'import-batches' => ['file_name' => 'leads.csv', 'file_path' => 'imports/leads.csv'],
        };
        $field = match ($resource) {
            'training-schedules' => 'location',
            'registrations', 'payments', 'commissions' => 'notes',
            'follow-ups' => 'title',
            'reminders' => 'message',
            'import-batches' => 'file_name',
            default => 'name',
        };
        $creator = $resource === 'registrations' ? $sales : $admin;
        $this->actingAs($creator);
        $id = $this->postJson('/api/'.$resource, $payload)->assertCreated()->json('data.id');
        $created = Activity::where('entity_type', $resource)->where('entity_id', $id)->where('type', 'created')->sole();
        $this->assertSame($creator->id, $created->user_id);
        $this->assertSame($creator->name, $created->actor_name);
        if ($resource === 'users') {
            $this->assertStringNotContainsString('Secret123!', json_encode($created->changes));
            $this->assertSame('[disembunyikan]', $created->changes['password']['after']);
        }

        $this->actingAs($admin);
        $this->patchJson('/api/v1/'.$resource.'/'.$id, [$field => 'Perubahan audit'])->assertOk();
        $updated = Activity::where('entity_type', $resource)->where('entity_id', $id)->where('type', 'updated')->latest('id')->firstOrFail();
        $this->assertSame('Perubahan audit', $updated->changes[$field]['after']);
        $this->assertSame($payload[$field] ?? null, $updated->changes[$field]['before']);

        $this->deleteJson('/api/'.$resource.'/'.$id)->assertNoContent();
        $deleted = Activity::where('entity_type', $resource)->where('entity_id', $id)->where('type', 'deleted')->sole();
        $this->assertSame('Perubahan audit', $deleted->changes[$field]['before']);
        $this->assertNull($deleted->changes[$field]['after']);
        $this->getJson('/api/activities?type=deleted')->assertOk()->assertJsonFragment(['id' => $deleted->id, 'entity_type' => $resource]);
    }

    public static function resources(): iterable
    {
        foreach (['leads', 'customers', 'institutions', 'trainers', 'products', 'lead-sources', 'rejection-reasons', 'users', 'training-schedules', 'registrations', 'payments', 'commissions', 'follow-ups', 'reminders', 'import-batches'] as $resource) {
            yield $resource => [$resource];
        }
    }

    public function test_noop_failed_validation_and_rolled_back_changes_do_not_create_history(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $id = $this->postJson('/api/institutions', ['name' => 'Mitra'])->assertCreated()->json('data.id');
        $count = Activity::count();
        $this->patchJson('/api/institutions/'.$id, ['name' => 'Mitra'])->assertOk();
        $this->postJson('/api/institutions', ['name' => ''])->assertUnprocessable();
        $this->assertSame($count, Activity::count());

        Route::post('/audit-failure-test', function () {
            Institution::create(['name' => 'Tidak jadi disimpan']);
            return response()->json(['message' => 'Gagal'], 422);
        })->middleware(AtomicCrmChanges::class);
        $this->postJson('/audit-failure-test')->assertUnprocessable();
        $this->assertDatabaseMissing('institutions', ['name' => 'Tidak jadi disimpan']);
        $this->assertSame($count, Activity::count());
    }

    public function test_bulk_read_logs_each_changed_reminder_and_does_not_repeat_noops(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create(['name' => 'Prospek', 'lead_code' => 'BULK', 'assigned_to' => $sales->id]);
        foreach (range(1, 2) as $number) {
            SalesReminder::create(['lead_id' => $lead->id, 'sales_id' => $sales->id, 'admin_id' => $admin->id, 'message' => 'Pengingat '.$number]);
        }
        $this->actingAs($sales);
        $this->postJson('/api/reminders/mark-all-read')->assertOk();
        $logs = Activity::where('entity_type', 'reminders')->where('type', 'updated')->get();
        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertSame($sales->id, $log->user_id);
            $this->assertEquals(0, $log->changes['is_read']['before']);
            $this->assertEquals(1, $log->changes['is_read']['after']);
        }
        $this->postJson('/api/reminders/mark-all-read')->assertOk();
        $this->assertSame(2, Activity::where('entity_type', 'reminders')->where('type', 'updated')->count());
    }

    public function test_sales_cannot_read_other_sales_history_or_modify_audit_records(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $this->actingAs($other);
        $lead = Lead::create(['name' => 'Rahasia Sales lain', 'lead_code' => 'PRIVATE', 'assigned_to' => $other->id]);
        $log = Activity::where('entity_type', 'leads')->where('entity_id', $lead->id)->sole();
        $this->actingAs($sales);
        $this->getJson('/api/activities')->assertOk()->assertJsonMissing(['entity_name' => 'Rahasia Sales lain']);
        $this->getJson('/api/activities/'.$log->id)->assertNotFound();
        $this->patchJson('/api/activities/'.$log->id, ['subject' => 'Diubah'])->assertForbidden();
        $this->deleteJson('/api/activities/'.$log->id)->assertForbidden();
    }

    public function test_history_survives_hard_delete_and_keeps_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);
        $lead = Lead::create(['name' => 'Prospek dihapus', 'lead_code' => 'DELETE']);
        $logId = Activity::where('entity_type', 'leads')->where('entity_id', $lead->id)->value('id');
        $lead->forceDelete();
        $admin->forceDelete();
        $log = Activity::findOrFail($logId);
        $this->assertNull($log->lead_id);
        $this->assertNull($log->user_id);
        $this->assertSame('Prospek dihapus', $log->entity_name);
        $this->assertSame($admin->name, $log->actor_name);
    }
}
