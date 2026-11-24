<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentCandidateTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_past_verified_payment_does_not_hide_a_new_registration_for_the_same_program(): void
    {
        Storage::fake('public');
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM']);
        $lead = Lead::create([
            'lead_code' => 'LDA-PESERTA',
            'name' => 'Peserta',
            'assigned_to' => $sales->id,
            'product_id' => $product->id,
            'status' => 'won',
        ]);
        $customer = Customer::create([
            'customer_code' => 'CUST-PESERTA',
            'name' => 'Peserta',
            'lead_id' => $lead->id,
        ]);
        $pastPayment = Payment::create([
            'lead_id' => $lead->id,
            'product_id' => $product->id,
            'amount' => 100000,
            'status' => 'valid',
        ]);
        $first = Registration::create(['customer_id' => $customer->id, 'product_id' => $product->id]);
        $this->assertSame('confirmed', $first->status);
        $this->assertSame($first->id, $pastPayment->fresh()->registration_id);

        $data = ['customer_id' => $customer->id, 'product_id' => $product->id];
        $this->actingAs($sales);
        $secondId = $this->postJson('/api/registrations', $data)->assertCreated()
            ->assertJsonPath('data.status', 'registered')->json('data.id');
        $thirdId = $this->postJson('/api/registrations', $data)->assertCreated()->json('data.id');
        $this->getJson('/api/registrations?for_payment=1&status=registered')
            ->assertOk()->assertJsonCount(2, 'data');

        $paymentId = $this->postJson('/api/payments', [
            'registration_id' => $secondId,
            'amount' => 100000,
            'proof_file' => UploadedFile::fake()->create('struk.png', 1, 'image/png'),
        ])->assertCreated()->assertJsonPath('data.registration_id', $secondId)->json('data.id');
        $this->getJson('/api/registrations?for_payment=1&status=registered')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $thirdId);

        $this->actingAs($admin)->patchJson("/api/payments/$paymentId/verify", ['status' => 'valid'])->assertOk();

        $this->assertDatabaseHas('registrations', ['id' => $secondId, 'status' => 'confirmed']);
        $this->assertDatabaseHas('registrations', ['id' => $thirdId, 'status' => 'registered']);
        $verification = Activity::where('entity_type', 'payments')->where('entity_id', $paymentId)
            ->where('type', 'updated')->latest('id')->firstOrFail();
        $this->assertSame($admin->id, $verification->user_id);
        $this->assertSame('pending', $verification->changes['status']['before']);
        $this->assertSame('valid', $verification->changes['status']['after']);
    }

    public function test_sales_can_pay_a_registration_that_does_not_have_a_lead_yet(): void
    {
        Storage::fake('public');
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM']);
        $registration = $this->actingAs($sales)->postJson('/api/registrations', [
            'participant_mode' => 'manual',
            'participant_name' => 'Peserta',
            'product_id' => $product->id,
        ])->assertCreated();
        $id = $registration->json('data.id');

        $this->getJson('/api/registrations?for_payment=1&status=registered')
            ->assertOk()->assertJsonPath('data.0.id', $id);
        $payment = $this->postJson('/api/payments', [
            'registration_id' => $id,
            'amount' => 100000,
            'proof_file' => UploadedFile::fake()->create('struk.png', 1, 'image/png'),
        ])->assertCreated()->assertJsonPath('data.registration_id', $id);

        $this->assertDatabaseHas('leads', ['id' => $payment->json('data.lead_id'), 'assigned_to' => $sales->id]);
        $this->assertDatabaseHas('customers', [
            'id' => $registration->json('data.customer_id'),
            'lead_id' => $payment->json('data.lead_id'),
        ]);
        $this->getJson('/api/registrations?for_payment=1&status=registered')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/leads?for_payment=1')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_a_prospect_with_a_submitted_payment_is_only_listed_if_a_resubmission_is_requested(): void
    {
        Storage::fake('public');
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM']);
        $lead = Lead::create([
            'lead_code' => 'LDA-PROSPEK',
            'name' => 'Prospek',
            'assigned_to' => $sales->id,
            'product_id' => $product->id,
        ]);
        $url = '/api/leads?for_payment=1';
        $this->actingAs($sales)->getJson($url)->assertOk()->assertJsonPath('data.0.id', $lead->id);

        $paymentId = $this->postJson('/api/payments', [
            'lead_id' => $lead->id,
            'amount' => 100000,
            'proof_file' => UploadedFile::fake()->create('struk.png', 1, 'image/png'),
        ])->assertCreated()->json('data.id');
        $this->getJson($url)->assertOk()->assertJsonCount(0, 'data');

        $payment = Payment::findOrFail($paymentId);
        $payment->update(['status' => 'recheck']);
        $this->getJson($url)->assertOk()->assertJsonPath('data.0.id', $lead->id);

        $payment->update(['status' => 'valid']);
        $this->getJson($url)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_a_preexisting_unlinked_payment_only_hides_the_matching_registration(): void
    {
        Storage::fake('public');
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM']);
        $lead = Lead::create([
            'lead_code' => 'LDA-PESERTA',
            'name' => 'Peserta',
            'assigned_to' => $sales->id,
            'product_id' => $product->id,
        ]);
        $customer = Customer::create(['customer_code' => 'CUST-PESERTA', 'name' => 'Peserta', 'lead_id' => $lead->id]);
        $data = ['customer_id' => $customer->id, 'product_id' => $product->id];
        $first = Registration::create($data);
        $second = Registration::create($data);
        $legacyPayment = Payment::create([
            'lead_id' => $lead->id,
            'product_id' => $product->id,
            'amount' => 100000,
            'status' => 'pending',
        ]);
        $this->travel(1)->seconds();
        $third = Registration::create($data);

        $ids = array_column($this->actingAs($sales)
            ->getJson('/api/registrations?for_payment=1&status=registered')
            ->assertOk()->json('data'), 'id');
        $this->assertEqualsCanonicalizing([$first->id, $third->id], $ids);
        $this->assertNotContains($second->id, $ids);
        $this->postJson('/api/payments', [
            'registration_id' => $first->id,
            'amount' => 100000,
            'proof_file' => UploadedFile::fake()->create('struk.png', 1, 'image/png'),
        ])->assertCreated()->assertJsonPath('data.registration_id', $first->id);
        $this->assertNull($legacyPayment->fresh()->registration_id);
    }

    public function test_repeat_order_classification_requires_a_previously_verified_payment(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $product = Product::create(['name' => 'Program Baru', 'code' => 'BARU']);
        $previousProduct = Product::create(['name' => 'Program Sebelumnya', 'code' => 'LAMA']);
        $expected = [];

        foreach (['pending' => false, 'valid' => true] as $paymentStatus => $isRepeatOrder) {
            $lead = Lead::create([
                'name' => 'Peserta '.$paymentStatus,
                'lead_code' => 'LDA-'.$paymentStatus,
                'assigned_to' => $sales->id,
                'product_id' => $product->id,
            ]);
            $customer = Customer::create([
                'name' => $lead->name,
                'customer_code' => 'CUST-'.$paymentStatus,
                'lead_id' => $lead->id,
                'status' => $isRepeatOrder ? 'active' : 'prospect',
            ]);
            Payment::create([
                'lead_id' => $lead->id,
                'product_id' => $previousProduct->id,
                'amount' => 100000,
                'status' => $paymentStatus,
            ]);
            $registration = Registration::create([
                'customer_id' => $customer->id,
                'user_id' => $sales->id,
                'product_id' => $product->id,
            ]);
            $expected[$registration->id] = $isRepeatOrder;
        }

        $registrations = $this->actingAs($sales)
            ->getJson('/api/registrations?for_payment=1&status=registered')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->json('data');

        foreach ($registrations as $registration) {
            $this->assertSame($expected[$registration['id']], $registration['is_repeat_order']);
        }
    }
}
