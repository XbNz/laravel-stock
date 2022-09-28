<?php

namespace Tests\Unit\Domain\TrackingRequests\Jobs;

use Domain\TrackingRequests\Enums\TrackingRequest as TrackingRequestEnum;
use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\FailedState;
use Domain\TrackingRequests\States\InProgressState;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Support\Contracts\StoreContract;
use Tests\TestCase;
use Tests\Unit\Fakes\FakeStore;

class ProcessStoreServiceCallJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function given_a_collection_of_a_users_search_tracking_requests_it_runs_then_through_the_store_service_search_method(): void
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
            Collection::make([$shouldBePutThroughSearchMethod]),
            $shouldBePutThroughSearchMethod->user,
            [$mockStore]
        ))->handle();

    }

    /** @test **/
    public function given_a_collection_of_a_users_product_tracking_requests_it_runs_then_through_the_store_service_product_method(): void
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
            Collection::make([$shouldBePutThroughProductMethod]),
            $shouldBePutThroughProductMethod->user,
            [$mockStore]
        ))->handle();

    }

    /** @test **/
    public function it_rejects_in_progress_tracking_requests(): void
    {
        // Arrange
        $shouldBePutThroughNothing = TrackingRequest::factory()->create([
            'tracking_type' => TrackingRequestEnum::SingleProduct,
            'status' => InProgressState::class,
        ]);

        $mockStore = $this->mock(StoreContract::class);
        $mockStore->shouldNotHaveBeenCalled();

        // Act

        (new ProcessStoreServiceCallJob(
            Collection::make([$shouldBePutThroughNothing]),
            $shouldBePutThroughNothing->user,
            [$mockStore]
        ))->handle();
    }

    /** @test **/
    public function it_rejects_failed_tracking_requests(): void
    {
        // Arrange
        $shouldBePutThroughNothing = TrackingRequest::factory()->create([
            'tracking_type' => TrackingRequestEnum::SingleProduct,
            'status' => FailedState::class,
        ]);

        $mockStore = $this->mock(StoreContract::class);
        $mockStore->shouldNotHaveBeenCalled();

        // Act

        (new ProcessStoreServiceCallJob(
            Collection::make([$shouldBePutThroughNothing]),
            $shouldBePutThroughNothing->user,
            [$mockStore]
        ))->handle();
    }

    /** @test **/
    public function after_completion_a_tracking_request_must_be_set_to_dormant(): void
    {
        // Arrange

        // TODO: Move the private methods on the job class to action classes responsible for updating stock. Then mock those in this test.

        // Act

        // Assert
    }

    /** @test **/
    public function a_failed_job_must_call_the_failed_notification_action(): void
    {
        // Arrange

        // Act

        // Assert
    }
}
