<?php

namespace Tests\Unit\Domain\TrackingRequests\Subscribers;

use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingRequestSubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function created_works(): void
    {
        // Arrange
        TrackingRequest::factory()->create();

        // Act

        // Assert
    }
}
