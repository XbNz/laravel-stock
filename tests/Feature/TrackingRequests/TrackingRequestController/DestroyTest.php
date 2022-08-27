<?php

declare(strict_types=1);

namespace Tests\Feature\TrackingRequests\TrackingRequestController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function upon_deletion_of_a_tracking_request_any_stocks_related_to_the_given_request_which_do_not_relate_to_any_remaining_tracking_request_records_of_the_authenticated_user_will_be_detached_from_the_users_stocks(): void
    {
        // Arrange

        // Act

        // Assert
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('trackingRequest.destroy', ['auth:sanctum']);
    }
}
