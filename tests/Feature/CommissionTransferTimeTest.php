<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Lead;
use App\Models\SalesReminder;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CommissionTransferTimeTest extends TestCase
{
    use RefreshDatabase;

    public static function transferTimes(): array
    {
        return [
            'WIB form without offset' => ['2026-11-23T01:59'],
            'explicit WIB offset' => ['2026-11-23T01:59:00+07:00'],
            'UTC API client' => ['2026-11-22T18:59:00Z'],
            'current server time' => [null],
        ];
    }

    #[DataProvider('transferTimes')]
    public function test_transfer_time_is_saved_as_utc_and_displayed_as_wib(?string $paidAt): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-22T18:59:00Z'));
        $commission = $this->createCommission();

        $response = $this->patchJson("/api/commissions/{$commission->id}/pay", ['paid_at' => $paidAt])
            ->assertOk();

        $stored = $commission->fresh();
        $this->assertSame('2026-11-22 18:59:00', $stored->getRawOriginal('paid_at'));
        $this->assertSame('2026-11-23 01:59', CarbonImmutable::parse($response->json('data.paid_at'))
            ->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'));
        $reminder = SalesReminder::where('commission_id', $commission->id)->firstOrFail();
        $this->assertStringContainsString('23 Nov 2026, 01:59 WIB', $reminder->message);

        $this->getJson('/api/commissions?date=2026-11-23')->assertOk()
            ->assertJsonPath('data.0.id', $commission->id);
        $this->getJson('/api/commissions?date=2026-11-22')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_invalid_transfer_time_is_rejected_without_marking_the_commission_paid(): void
    {
        $commission = $this->createCommission();
        $this->patchJson("/api/commissions/{$commission->id}/pay", ['paid_at' => 'not-a-date'])
            ->assertUnprocessable()->assertJsonValidationErrors('paid_at');
        $this->assertSame('approved', $commission->fresh()->status);
        $this->assertNull($commission->fresh()->paid_at);
    }

    private function createCommission(): Commission
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $lead = Lead::create(['lead_code' => 'LDA-TIME', 'name' => 'Rizky', 'assigned_to' => $sales->id]);
        $commission = Commission::create([
            'user_id' => $sales->id,
            'lead_id' => $lead->id,
            'deal_amount' => 4273500,
            'commission_amount' => 388500,
            'commission_rate' => 10,
            'status' => 'approved',
        ]);
        $this->actingAs($admin);

        return $commission;
    }
}
