<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CrmCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generators_use_the_latest_code_for_the_correct_period(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 10:00:00'));
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM']);
        $lead = Lead::create(['name' => 'Prospek', 'lead_code' => 'LDA-20261120-0007']);
        $customer = Customer::create(['name' => 'Peserta', 'customer_code' => 'CUST-202611-0008']);
        Registration::create([
            'registration_number' => 'REG-20261120-0009',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);
        $lead->delete();
        $customer->delete();

        $this->assertSame('LDA-20261120-0008', Lead::generateCode());
        $this->assertSame('CUST-202611-0009', Customer::generateCode());
        $this->assertSame('REG-20261120-0010', Registration::generateNumber());

        $this->travelTo(Carbon::parse('2026-11-21 10:00:00'));
        $this->assertSame('LDA-20261121-0001', Lead::generateCode());
        $this->assertSame('CUST-202611-0009', Customer::generateCode());
        $this->assertSame('REG-20261121-0001', Registration::generateNumber());

        $this->travelTo(Carbon::parse('2026-12-01 10:00:00'));
        $this->assertSame('CUST-202612-0001', Customer::generateCode());
    }
}
