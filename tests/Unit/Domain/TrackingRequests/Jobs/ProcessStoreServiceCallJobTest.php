<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TrackingRequests\Jobs;

use Domain\Alerts\Models\AlertChannel;
use Domain\Stocks\Models\Stock;
use Domain\Stores\DTOs\StockData;
use Domain\TrackingRequests\Enums\TrackingRequest as TrackingRequestEnum;
use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\Notifications\TrackingRequestFailedNotification;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\FailedState;
use Domain\TrackingRequests\States\InProgressState;
use Domain\TrackingRequests\States\RecoveryState;
use Exception;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Support\Contracts\StoreContract;
use Tests\TestCase;
use Tests\Unit\Fakes\FakeStore;

class ProcessStoreServiceCallJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function given_a_search_tracking_request_it_runs_then_through_the_store_service_search_method(): void
    {
        // Arrange
        $shouldBePutThroughSearchMethod = TrackingRequest::factory()->create([
            'tracking_type' => TrackingRequestEnum::Search,
            'status' => DormantState::class,
        ]);

        $mockStore = $this->mock(StoreContract::class);
        $mockStore->shouldReceive('supports')->andReturnTrue();
        $mockStore->shouldReceive('search')
            ->with([new Uri($shouldBePutThroughSearchMethod->url)])
            ->once();

        // Act

        (new ProcessStoreServiceCallJob(
            $shouldBePutThroughSearchMethod,
            [$mockStore]
        ))->handle();
    }

    /** @test **/
    public function given_a_product_tracking_request_it_runs_then_through_the_store_service_product_method(): void
    {
        // Arrange
        $shouldBePutThroughProductMethod = TrackingRequest::factory()->create([
            'tracking_type' => TrackingRequestEnum::SingleProduct,
            'status' => DormantState::class,
        ]);

        $mockStore = $this->mock(StoreContract::class);
        $mockStore->shouldReceive('supports')->andReturnTrue();
        $mockStore->shouldReceive('product')
            ->with([new Uri($shouldBePutThroughProductMethod->url)])
            ->once();

        // Act

        (new ProcessStoreServiceCallJob(
            $shouldBePutThroughProductMethod,
            [$mockStore]
        ))->handle();
    }

    /** @test **/
    public function after_completion_a_tracking_request_must_be_set_to_dormant(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create([
            'tracking_type' => TrackingRequestEnum::SingleProduct,
            'status' => DormantState::class,
            'url' => 'https://www.amazon.co.uk/dp/B07ZJZ2Z9Z',
        ]);

        $mockStore = $this->mock(StoreContract::class);
        $mockStore->shouldReceive('supports')->andReturnTrue();
        $mockStore->shouldReceive('product')
            ->with([new Uri($trackingRequest->url)])
            ->andReturn([
                StockData::generateFake([
                    'link' => new Uri($trackingRequest->url),
                ]),
            ]);

        // Act

        (new ProcessStoreServiceCallJob(
            $trackingRequest,
            [$mockStore]
        ))->handle();

        // Assert

        $this->assertTrue($trackingRequest->wasChanged());
        $this->assertInstanceOf(DormantState::class, $trackingRequest->status);
    }

    /** @test **/
    public function a_failed_job_must_call_the_failed_notification_action_and_log_the_context(): void
    {
        // Arrange
        Notification::fake();
        Log::shouldReceive('warning')->once();

        $trackingRequest = TrackingRequest::factory()->create([
            'tracking_type' => TrackingRequestEnum::SingleProduct,
            'status' => DormantState::class,
            'url' => 'https://www.amazon.co.uk/dp/B07ZJZ2Z9Z',
        ]);

        $channel = AlertChannel::factory()->verificationNotRequiredChannel()->create([
            'user_id' => $trackingRequest->user_id,
        ]);

        // Act

        (new ProcessStoreServiceCallJob(
            $trackingRequest,
            [new FakeStore()]
        ))->failed(new Exception('test'));

        // Assert

        Notification::assertSentTo($channel, TrackingRequestFailedNotification::class);
    }

    /** @test **/
    public function requests_are_sent_to_recovery_based_on_confidence_percentage(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->has(Stock::factory())->create([
            'status' => InProgressState::class,
        ]);

        // Act
        (new ProcessStoreServiceCallJob(
            $trackingRequest,
            [new FakeStore()]
        ))->failed(new Exception('test'));

        // Assert

        $this->assertTrue($trackingRequest->status->equals(RecoveryState::class));
    }

    /** @test **/
    public function requests_are_sent_to_failed_based_on_confidence_percentage(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create([
            'status' => InProgressState::class,
        ]);

        // Act
        (new ProcessStoreServiceCallJob(
            $trackingRequest,
            [new FakeStore()]
        ))->failed(new Exception('test'));

        // Assert

        $this->assertTrue($trackingRequest->status->equals(FailedState::class));
    }

}
