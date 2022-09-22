<?php

namespace Tests\Unit\Domain\Stocks\Actions;

use Domain\Stocks\Actions\FormatPriceAction;
use Domain\Stores\Enums\Currency;
use Generator;
use Tests\TestCase;

class FormatPriceActionTest extends TestCase
{
    /**
     * @test
     * @dataProvider cadProvider
     */
    public function it_formats_price_cad(int $priceInCents, string $expectedPrice): void
    {
        $price = app(FormatPriceAction::class)($priceInCents, Currency::CAD);

        $this->assertSame($expectedPrice, $price);
    }

    /**
     * @test
     * @dataProvider usdProvider
     */
    public function it_formats_price_usd(int $priceInCents, string $expectedPrice): void
    {
        $price = app(FormatPriceAction::class)($priceInCents, Currency::USD);

        $this->assertSame($expectedPrice, $price);
    }

    /**
     * @test
     * @dataProvider eurProvider
     */
    public function it_formats_price_eur(int $priceInCents, string $expectedPrice): void
    {
        $price = app(FormatPriceAction::class)($priceInCents, Currency::EUR);

        $this->assertSame($expectedPrice, $price);
    }

    /**
     * @test
     * @dataProvider gbpProvider
     */
    public function it_formats_price_gbp(int $priceInCents, string $expectedPrice): void
    {
        $price = app(FormatPriceAction::class)($priceInCents, Currency::GBP);

        $this->assertSame($expectedPrice, $price);
    }

    public function cadProvider(): Generator
    {
        yield from [
            [100, '$1.00'],
            [1000, '$10.00'],
            [10000, '$100.00'],
            [100000, '$1,000.00'],
            [1000000, '$10,000.00'],
            [10000000, '$100,000.00'],
            [100000000, '$1,000,000.00'],
            [1000000000, '$10,000,000.00'],
            [10000000000, '$100,000,000.00'],
        ];
    }

    public function usdProvider(): Generator
    {
        yield from [
            [100, '$1.00'],
            [1000, '$10.00'],
            [10000, '$100.00'],
            [100000, '$1,000.00'],
            [1000000, '$10,000.00'],
            [10000000, '$100,000.00'],
            [100000000, '$1,000,000.00'],
            [1000000000, '$10,000,000.00'],
            [10000000000, '$100,000,000.00'],
        ];
    }

    public function eurProvider(): Generator
    {
        yield from [
            [100, '€1,00'],
            [1000, '€10,00'],
            [10000, '€100,00'],
            [100000, '€1.000,00'],
            [1000000, '€10.000,00'],
            [10000000, '€100.000,00'],
            [100000000, '€1.000.000,00'],
            [1000000000, '€10.000.000,00'],
            [10000000000, '€100.000.000,00'],
        ];
    }

    public function gbpProvider(): Generator
    {
        yield from [
            [100, '£1.00'],
            [1000, '£10.00'],
            [10000, '£100.00'],
            [100000, '£1,000.00'],
            [1000000, '£10,000.00'],
            [10000000, '£100,000.00'],
            [100000000, '£1,000,000.00'],
            [1000000000, '£10,000,000.00'],
            [10000000000, '£100,000,000.00'],
        ];
    }

}
