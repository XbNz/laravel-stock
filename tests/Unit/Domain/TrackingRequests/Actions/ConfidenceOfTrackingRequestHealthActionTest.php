<?php

namespace Tests\Unit\Domain\TrackingRequests\Actions;

use Domain\Stocks\Models\Stock;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Actions\ConfidenceOfTrackingRequestHealthAction;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Support\ValueObjects\Percentage;
use Tests\TestCase;

class ConfidenceOfTrackingRequestHealthActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function given_a_tracking_request_it_returns_a_confience_percentage_that_it_is_a_legit_link(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();

        // Act
        $confidence = app(ConfidenceOfTrackingRequestHealthAction::class)($trackingRequest);

        // Assert
        $this->assertEquals(0, $confidence->value);
    }

    /** @test **/
    public function tracking_request_with_stocks_should_have_a_fair_amount_of_confidence(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()
            ->has(Stock::factory()->count(3))
            ->create();

        // Act
        $confidence = app(ConfidenceOfTrackingRequestHealthAction::class)($trackingRequest);

        // Assert
        $this->assertGreaterThan(20, $confidence->value);
    }

    /** @test **/
    public function if_other_requests_from_the_given_store_have_been_updated_later_than_subject_then_there_might_be_something_wrong_with_subject_request(): void
    {
        // Arrange
        $store = Arr::random(Store::cases());
        $trackingRequestA = TrackingRequest::factory()->create(['store' => $store]);
        $trackingRequestB = TrackingRequest::factory()->create(['store' => $store]);

        $this->travel(1)->minutes();
        $trackingRequestB->touch();

        // Act
        $confidenceA = app(ConfidenceOfTrackingRequestHealthAction::class)($trackingRequestA);
        $confidenceB = app(ConfidenceOfTrackingRequestHealthAction::class)($trackingRequestB);

        // Assert
        $this->assertGreaterThan($confidenceA->value, $confidenceB->value);
    }
}
