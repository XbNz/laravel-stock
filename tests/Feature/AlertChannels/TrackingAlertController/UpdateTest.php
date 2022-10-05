<?php

declare(strict_types=1);

namespace Tests\Feature\AlertChannels\TrackingAlertController;

use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function the_owner_of_a_tracking_alert_can_update_it(): void
    {
        // Arrange
        $trackingAlertA = TrackingAlert::factory()->create([
            'percentage_trigger' => 50,
            'availability_trigger' => true,
        ]);
        $trackingAlertB = TrackingAlert::factory()->create();
        $newAlertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create([
            'user_id' => $trackingAlertA->user->id,
        ]);

        Sanctum::actingAs($trackingAlertA->user);

        // Act
        $responseA = $this->json('PUT', route('trackingAlert.update', [
            'trackingAlert' => $trackingAlertA->uuid,
        ]), [
            'alert_channel_uuid' => $newAlertChannel->uuid,
            'percentage_trigger' => 51,
            'availability_trigger' => false,
        ]);
        $responseB = $this->json('PUT', route('trackingAlert.update', [
            'trackingAlert' => $trackingAlertB->uuid,
        ]), [
            'alert_channel_uuid' => $newAlertChannel->uuid,
            'percentage_trigger' => 1,
            'availability_trigger' => true,
        ]);

        // Assert
        $responseA->assertOk();
        $responseB->assertStatus(Response::HTTP_NOT_FOUND);

        $responseA->assertJson([
            'data' => [
                'alert_channel' => [
                    'uuid' => $newAlertChannel->uuid,
                ],
                'percentage_trigger' => 51,
                'availability_trigger' => false,
            ],
        ]);

        $this->assertDatabaseHas('tracking_alerts', [
            'user_id' => $trackingAlertA->user->id,
            'alert_channel_id' => $newAlertChannel->id,
            'percentage_trigger' => 51,
            'availability_trigger' => false,
        ]);
    }

    /** @test **/
    public function the_tracking_request_may_only_be_updated_with_a_new_channel_that_belongs_to_the_logged_in_user(): void
    {
        // Arrange
        $trackingAlert = TrackingAlert::factory()->create();
        $newAlertChannelA = AlertChannel::factory()->verificationNotRequiredChannel()->create([
            'user_id' => $trackingAlert->user->id,
        ]);
        $newAlertChannelB = AlertChannel::factory()->verificationNotRequiredChannel()->create();

        Sanctum::actingAs($trackingAlert->user);

        // Act
        $responseA = $this->json('PUT', route('trackingAlert.update', [
            'trackingAlert' => $trackingAlert->uuid,
        ]), [
            'alert_channel_uuid' => $newAlertChannelA->uuid,
        ]);
        $responseB = $this->json('PUT', route('trackingAlert.update', [
            'trackingAlert' => $trackingAlert->uuid,
        ]), [
            'alert_channel_uuid' => $newAlertChannelB->uuid,
        ]);

        // Assert

        $responseA->assertOk();
        $responseB->assertJsonValidationErrorFor('alert_channel_uuid');
    }

    /** @test **/
    public function if_an_alert_channel_requires_verification_but_has_not_been_verified_it_may_not_be_attached_to_a_tracking_alert(): void
    {
        // Arrange
        $trackingAlert = TrackingAlert::factory()->create();
        $alertChannel = AlertChannel::factory()->verificationRequiredChannel()->create([
            'user_id' => $trackingAlert->user->id,
        ]);

        Sanctum::actingAs($trackingAlert->user);

        // Act
        $response = $this->json('PUT', route('trackingAlert.update', [
            'trackingAlert' => $trackingAlert->uuid,
        ]), [
            'alert_channel_uuid' => $alertChannel->uuid,
        ]);

        // Assert
        $response->assertJsonValidationErrorFor('alert_channel_uuid');
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('trackingAlert.update', ['auth:sanctum']);
    }

    /**
     * @test
     * @dataProvider validationProvider
     **/
    public function validation_tests(array $payload, string $error): void
    {
        // Arrange
        $trackingAlert = TrackingAlert::factory()->create([
            'percentage_trigger' => 50,
            'availability_trigger' => true,
        ]);
        Sanctum::actingAs($trackingAlert->user);

        // Act
        $response = $this->json('PUT', route('trackingAlert.update', [
            'trackingAlert' => $trackingAlert->uuid,
        ]), $payload);

        // Assert
        $response->assertJsonValidationErrorFor($error);

        $this->assertDatabaseHas('tracking_alerts', [
            'user_id' => $trackingAlert->user->id,
            'percentage_trigger' => $trackingAlert->percentage_trigger,
            'availability_trigger' => $trackingAlert->availability_trigger,
        ]);
    }

    public function validationProvider(): Generator
    {
        $good = [
            'percentage_trigger' => 60,
            'availability_trigger' => true,
        ];

        yield from [
            'non_integer_percentage_trigger' => [
                'payload' => array_merge($good, [
                    'percentage_trigger' => '::gibberish::',
                ]),
                'error' => 'percentage_trigger',
            ],
            'non_boolean_availability_trigger' => [
                'payload' => array_merge($good, [
                    'availability_trigger' => '::gibberish::',
                ]),
                'error' => 'availability_trigger',
            ],
            'percentage_over_100' => [
                'payload' => array_merge($good, [
                    'percentage_trigger' => 101,
                ]),
                'error' => 'percentage_trigger',
            ],
            'percentage_under_1' => [
                'payload' => array_merge($good, [
                    'percentage_trigger' => 0,
                ]),
                'error' => 'percentage_trigger',
            ],
        ];
    }
}
