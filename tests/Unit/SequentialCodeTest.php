<?php

namespace Tests\Unit;

use App\Support\SequentialCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SequentialCodeTest extends TestCase
{
    #[DataProvider('codeSequences')]
    public function test_it_formats_the_next_crm_code(string $prefix, ?string $lastCode, string $expected): void
    {
        $this->assertSame($expected, SequentialCode::next($prefix, $lastCode));
    }

    public static function codeSequences(): array
    {
        return [
            'first lead of the day' => ['LDA-20261120', null, 'LDA-20261120-0001'],
            'next lead of the day' => ['LDA-20261120', 'LDA-20261120-0009', 'LDA-20261120-0010'],
            'first customer of the month' => ['CUST-202611', null, 'CUST-202611-0001'],
            'next customer of the month' => ['CUST-202611', 'CUST-202611-0099', 'CUST-202611-0100'],
            'first registration of the day' => ['REG-20261120', null, 'REG-20261120-0001'],
            'next registration of the day' => ['REG-20261120', 'REG-20261120-0999', 'REG-20261120-1000'],
        ];
    }
}
