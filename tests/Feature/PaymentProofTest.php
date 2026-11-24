<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Activity;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_attach_a_missing_receipt_without_changing_verification(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $lead = Lead::create(['lead_code' => 'LDA-STRUK', 'name' => 'Peserta Pelatihan']);
        $verifiedAt = now()->startOfSecond();
        $payment = Payment::create([
            'lead_id' => $lead->id,
            'amount' => 976800,
            'status' => 'valid',
            'verified_by' => $admin->id,
            'verified_at' => $verifiedAt,
        ]);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9lQAAAAASUVORK5CYII=');
        $url = "/api/payments/{$payment->id}/proof";

        $response = $this->actingAs($admin)
            ->post($url, ['proof_file' => UploadedFile::fake()->createWithContent('struk.png', $png)])
            ->assertOk()
            ->assertJsonPath('data.id', $payment->id)
            ->assertJsonPath('data.status', 'valid');
        $path = $response->json('data.proof_file');

        $this->assertSame($png, Storage::disk('public')->get($path));
        $this->assertStringEndsWith('/storage/'.$path, $response->json('data.proof_url'));
        $payment->refresh();
        $this->assertEquals($verifiedAt, $payment->verified_at);
        $this->assertSame($admin->id, $payment->verified_by);

        $history = Activity::where('entity_type', 'payments')->where('entity_id', $payment->id)
            ->where('type', 'updated')->sole();
        $this->assertSame($admin->id, $history->user_id);
        $this->assertSame($path, $history->changes['proof_file']['after']);
        $this->assertNull($history->changes['proof_file']['before']);

        $this->post($url, ['proof_file' => UploadedFile::fake()->createWithContent('struk-lain.png', $png)])
            ->assertConflict();
        $this->assertSame([$path], Storage::disk('public')->allFiles('payments'));
        $this->assertSame(1, Activity::where('entity_type', 'payments')->where('entity_id', $payment->id)->where('type', 'updated')->count());
    }

    public function test_sales_must_include_an_image_when_submitting_payment_evidence(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create(['lead_code' => 'LDA-STRUK', 'name' => 'Peserta', 'assigned_to' => $sales->id]);

        $this->actingAs($sales)
            ->postJson('/api/payments', ['lead_id' => $lead->id, 'amount' => 100000])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('proof_file');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_public_storage_fallback_route_serves_existing_proof_and_blocks_invalid_paths(): void
    {
        Storage::fake('public');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9lQAAAAASUVORK5CYII=');
        Storage::disk('public')->put('payments/fallback-proof.png', $png);

        $this->get('/storage/payments/fallback-proof.png')
            ->assertOk();

        $this->get('/storage/payments/tidak-ada.png')
            ->assertNotFound();

        $this->assertTrue(in_array($this->get('/storage/payments/..%2F.env')->getStatusCode(), [403, 404], true));
    }
}
