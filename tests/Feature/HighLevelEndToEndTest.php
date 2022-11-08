<?php

namespace Tests\Feature;

use Domain\TrackingRequests\Actions\FulfillTrackingRequestAction;
use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HighLevelEndToEndTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group Online
     **/
    public function a_new_tracking_request_enters_the_system_causing_new_stocks_to_be_created(): void
    {
        Notification::fake();

        Config::set([
            'store.Domain\Stores\Services\AmazonCanada\AmazonCanadaService.proxy' => false,
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->json('POST', route('trackingRequest.store'), [
            'url' => 'https://www.amazon.ca/s?k=iphone',
            'name' => 'iPhone',
            'update_interval' => 60,
        ]);

        $trackingRequest = TrackingRequest::sole();

        app(FulfillTrackingRequestAction::class)($trackingRequest);

        $this->assertGreaterThanOrEqual(1, $trackingRequest->stocks()->count());
        $this->assertGreaterThanOrEqual(1, $user->stocks()->count());
    }
}
