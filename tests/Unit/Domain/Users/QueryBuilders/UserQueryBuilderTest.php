<?php

namespace Tests\Unit\Domain\Users\QueryBuilders;

use Database\Factories\StockFactory;
use Domain\Stocks\Models\Stock;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserQueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function it_returns_all_users_that_are_associated_with_a_given_stock(): void
    {
        // Arrange

        $stockA = Stock::factory(state: ['price' => 1000]);
        $userA = User::factory()->has($stockA)->create();

        $stockB = Stock::factory(state: ['price' => 2000]);
        $userB = User::factory()->has($stockB)->create();

        $stockA = $userA->stocks()->sole();
        $stockB = $userB->stocks()->sole();

        // Act

        $shouldBeUserA = User::query()->whereHasStock($stockA)->sole();
        $shouldBeUserB = User::query()->whereHasStock($stockB)->sole();

        // Assert

        $this->assertTrue($shouldBeUserA->is($userA));
        $this->assertTrue($shouldBeUserB->is($userB));
    }
}
