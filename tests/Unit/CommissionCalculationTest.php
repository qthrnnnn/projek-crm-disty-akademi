<?php

namespace Tests\Unit;

use App\Models\Commission;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CommissionCalculationTest extends TestCase
{
    #[DataProvider('commissionAmounts')]
    public function test_it_calculates_the_deal_and_ten_percent_commission(
        float $catalogPrice,
        ?float $paymentAmount,
        ?float $negotiatedPrice,
        float $expectedDeal,
        float $expectedCommission,
    ): void {
        $this->assertSame([
            'deal_amount' => $expectedDeal,
            'commission_amount' => $expectedCommission,
        ], Commission::calculateAmounts($catalogPrice, $paymentAmount, $negotiatedPrice));
    }

    public static function commissionAmounts(): array
    {
        return [
            'catalog price rounded to cents' => [12345.67, null, null, 13580.24, 1234.57],
            'negotiated price with a catalog' => [1000000.0, null, 1050000.0, 1050000.0, 100000.0],
            'payment takes precedence over negotiated price' => [1000000.0, 1200000.0, 1050000.0, 1200000.0, 100000.0],
            'payment includes commission with no catalog' => [0.0, 1100000.0, null, 1100000.0, 100000.0],
            'fallback amount with no catalog' => [0.0, null, null, 1831500.0, 166500.0],
        ];
    }
}
