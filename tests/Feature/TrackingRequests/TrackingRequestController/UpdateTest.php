<?php

declare(strict_types=1);

namespace Tests\Feature\TrackingRequests\TrackingRequestController;

use Domain\TrackingRequests\Models\TrackingRequest;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function the_owner_of_a_tracking_request_can_update_only_its_update_interval(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->create([
            'update_interval' => 35,
        ]);
        $trackingRequestB = TrackingRequest::factory()->create();
        Sanctum::actingAs($trackingRequestA->user);

        // Act
        $responseA = $this->json('PUT', route('trackingRequest.update', [
            'trackingRequest' => $trackingRequestA->uuid,
        ]), [
            'name' => '::new-name::',
            'update_interval' => 55,
        ]);
        $responseB = $this->json('PUT', route('trackingRequest.update', [
            'trackingRequest' => $trackingRequestB->uuid,
        ]), [
            'name' => '::new-name::',
            'update_interval' => 55,
        ]);

        // Assert
        $responseA->assertOk();
        $responseB->assertNotFound();
        $responseA->assertJsonFragment([
            'update_interval' => 55,
        ]);

        $this->assertDatabaseHas('tracking_requests', [
            'name' => '::new-name::',
            'user_id' => $trackingRequestA->user->id,
            'update_interval' => 55,
        ]);
        $this->assertDatabaseMissing('tracking_requests', [
            'user_id' => $trackingRequestB->user->id,
            'update_interval' => 55,
        ]);
    }

    /**
     * @test
     * @dataProvider validationProvider
     **/
    public function validation_tests(array $payload, array $errors): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();
        Sanctum::actingAs($trackingRequest->user);

        // Act
        $response = $this->json('PUT', route('trackingRequest.update', [
            'trackingRequest' => $trackingRequest->uuid,
        ]), $payload);

        // Assert
        $response->assertJsonValidationErrorFor(...$errors);
    }

    public function validationProvider(): Generator
    {
        $default = [
            'name' => '::name::',
            'update_interval' => 35,
        ];

        yield from [
            'name must be a string' => [
                'payload' => array_merge($default, [
                    'name' => 123,
                ]),
                'errors' => ['name'],
            ],
            'name must be less than 255 characters' => [
                'payload' => array_merge($default, [
                    'name' => str_repeat('a', 256),
                ]),
                'errors' => ['name'],
            ],
            'update_interval must be an integer' => [
                'payload' => array_merge($default, [
                    'update_interval' => '::abc::',
                ]),
                'errors' => ['update_interval'],
            ],
            'update_interval must be above 30 seconds' => [
                'payload' => array_merge($default, [
                    'update_interval' => 29,
                ]),
                'errors' => ['update_interval'],
            ],
        ];
    }
}
