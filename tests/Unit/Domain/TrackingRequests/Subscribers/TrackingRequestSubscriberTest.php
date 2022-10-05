<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TrackingRequests\Subscribers;

use Domain\Stocks\Models\Stock;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingRequestSubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function updating_a_tracking_request_with_new_stocks_syncs_those_stocks_to_the_tracking_request_users_stocks(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->has(Stock::factory()->count(5))->create();
        $this->travel(1)->minutes();
        // Act
        $trackingRequest->touch();

        // Assert
        $this->assertSame(5, $trackingRequest->user->stocks()->count());
    }

    /** @test **/
    public function sync_happens_without_detaching_current_user_stocks(): void
    {
        // Arrange
        $user = User::factory()->has(Stock::factory()->count(5))->create();
        $trackingRequest = TrackingRequest::factory()->for($user)->has(Stock::factory()->count(5))->create();
        $this->travel(1)->minutes();

        // Act
        $trackingRequest->touch();

        // Assert
        $this->assertSame(10, $trackingRequest->user->stocks()->count());
    }
}
