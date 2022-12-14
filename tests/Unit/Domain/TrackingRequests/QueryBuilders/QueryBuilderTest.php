<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TrackingRequests\QueryBuilders;

use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\Stock;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function filters_stock(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->has(Stock::factory(state: [
            'price' => 100,
        ]))->create();
        $trackingRequestB = TrackingRequest::factory()->has(Stock::factory(state: [
            'price' => 200,
        ]))->create();

        // Act
        $trackingRequests = TrackingRequest::query()
            ->whereStock(Stock::first())
            ->get();

        // Assert
        $this->assertCount(1, $trackingRequests);
        $this->assertTrue($trackingRequests->contains($trackingRequestA));
        $this->assertSame(100, $trackingRequests->sole()->stocks()->sole()->getRawOriginal()['price']);
    }

    /** @test **/
    public function filters_tracking_alert(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->has(TrackingAlert::factory(state: [
            'percentage_trigger' => 50,
        ]))->create();
        $trackingRequestB = TrackingRequest::factory()->has(TrackingAlert::factory(state: [
            'percentage_trigger' => 100,
        ]))->create();

        // Act
        $trackingRequests = TrackingRequest::query()
            ->whereHasAlert(TrackingAlert::first())
            ->get();

        // Assert
        $this->assertCount(1, $trackingRequests);
        $this->assertTrue($trackingRequests->contains($trackingRequestA));
        $this->assertSame(50, $trackingRequests->sole()->trackingAlerts()->sole()->getRawOriginal()['percentage_trigger']);
    }

    /** @test **/
    public function filters_url(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->create([
            'url' => 'https://example.com',
        ]);
        $trackingRequestB = TrackingRequest::factory()->create([
            'url' => 'https://example.org',
        ]);

        // Act
        $trackingRequests = TrackingRequest::query()
            ->whereUrl(new Uri('https://example.com'))
            ->get();

        // Assert
        $this->assertCount(1, $trackingRequests);
        $this->assertTrue($trackingRequests->contains($trackingRequestA));
    }

    /** @test **/
    public function dormant_requests_whose_update_intervals_have_passed_should_be_picked_up(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->create(
            [
                'updated_at' => now(),
                'update_interval' => 30,
                'status' => DormantState::class,
            ]
        );
        $trackingRequestB = TrackingRequest::factory()->create(
            [
                'updated_at' => now()->subMinutes(10),
                'update_interval' => 30,
                'status' => DormantState::class,
            ]
        );
        $trackingRequestC = TrackingRequest::factory()->create(
            [
                'updated_at' => now()->subMinutes(10),
                'update_interval' => 599,
                'status' => DormantState::class,
            ]
        );

        // Act
        $needingUpdate = TrackingRequest::query()->needsUpdate();

        // Assert
        $this->assertEquals(2, $needingUpdate->count());
    }
}
