<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Alerts\QueryBuilders;

use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\Stock;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingAlertQueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function where_interested_in_stock(): void
    {
        // Arrange
        $stock = Stock::factory()->create();
        $trackingRequest = TrackingRequest::factory()->has(TrackingAlert::factory())->count(5)->create();
        $trackingRequest[0]->stocks()->attach($stock);

        // Act
        $trackingAlerts = TrackingAlert::query()->whereInterestedIn($stock);

        // Assert

        $this->assertEquals(1, $trackingAlerts->count());
    }
}
