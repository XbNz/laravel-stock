<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TrackingRequests\Actions;

use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Actions\FulfillTrackingRequestAction;
use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FulfillTrackingRequestActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function given_a_list_of_tracking_requests_it_invokes_a_job_per_each_user(): void
    {
        // Arrange
        Queue::fake();

        $trackingRequests = TrackingRequest::factory()->createMany([
            [
                'store' => Store::AmazonCanada,
                'url' => 'https://amazon.ca/eowfj-wg-ewweg-weg',
                'status' => DormantState::class,
            ],
            [
                'store' => Store::BestBuyCanada,
                'url' => 'https://bestbuy.ca/eowfj-wg-ewweg-weg',
                'status' => DormantState::class,
            ],
            [
                'store' => Store::NeweggCanada,
                'url' => 'https://newegg.ca/eowfj-wg-ewweg-weg',
                'status' => DormantState::class,
            ],
        ]);

        $action = app(FulfillTrackingRequestAction::class);

        // Act

        foreach ($trackingRequests as $trackingRequest) {
            $action($trackingRequest);
        }

        // Assert
        Queue::assertPushed(ProcessStoreServiceCallJob::class, 3);
    }
}
