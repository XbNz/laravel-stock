<?php

namespace Tests\Unit\Domain\Stores\Actions;

use Domain\Stores\Actions\ParseStoreByLinkAction;
use Domain\Stores\Enums\Store;
use PHPUnit\Framework\TestCase;

class ParseStoreByLinkActionTest extends TestCase
{
    /** @test **/
    public function it_takes_a_valid_store_link_and_returns_a_store_enum(): void
    {
        // Arrange
        $action = app(ParseStoreByLinkAction::class);

        $amazon = 'https://amazon.ca';
        $bestbuy = 'https://www.bestbuy.ca';
        $newegg = 'https://www.newegg.ca';

        // Act

        $shouldBeAmazon = ($action)($amazon);
        $shouldBeBestBuy = ($action)($bestbuy);
        $shouldBeNewegg = ($action)($newegg);

        // Assert

        $this->assertSame(Store::AmazonCanada, $shouldBeAmazon);
        $this->assertSame(Store::BestBuyCanada, $shouldBeBestBuy);
        $this->assertSame(Store::NeweggCanada, $shouldBeNewegg);
    }
}
