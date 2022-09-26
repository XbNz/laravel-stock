<?php

namespace Tests\Unit\Domain\TrackingRequests\Actions;

use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Actions\FulfillTrackingRequestAction;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FulfillTrackingRequestActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function given_a_list_of_tracking_requests_it_hits_their_respective_service_classes_with_the_uri_of_the_tracking_request(): void
    {
        // Arrange
        $trackingRequests = TrackingRequest::factory()->createMany([
            [
                'store' => Store::AmazonCanada,
                'url' => 'https://amazon.ca/eowfj-wg-ewweg-weg'
            ],
            [
                'store' => Store::BestBuyCanada,
                'url' => 'https://bestbuy.ca/eowfj-wg-ewweg-weg'
            ],
            [
                'store' => Store::NeweggCanada,
                'url' => 'https://newegg.ca/eowfj-wg-ewweg-weg'
            ]
        ]);


        $action = app(FulfillTrackingRequestAction::class);

        // Act
        ($action)($trackingRequests);


        // Assert
    }
}
