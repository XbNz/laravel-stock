<?php

declare(strict_types=1);

namespace Tests\Feature\AlertChannels\TrackingAlertController;

use Domain\Alerts\Models\AlertChannel;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test **/
    public function a_logged_in_user_can_store_a_new_tracking_alert(): void
    {
        // Arrange
        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        Sanctum::actingAs($alertChannel->user);

        // Act
        $response = $this->json('POST', route('trackingAlert.store'), [
            'alert_channel_uuid' => $alertChannel->uuid,
            'percentage_trigger' => 50,
            'availability_trigger' => true,
        ]);

        // Assert

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'alert_channel' => [
                    'uuid' => $alertChannel->uuid,
                ],
                'percentage_trigger' => '50',
                'availability_trigger' => true,
            ],
        ]);

        $this->assertDatabaseHas('tracking_alerts', [
            'user_id' => $alertChannel->user->id,
            'alert_channel_id' => $alertChannel->id,
            'percentage_trigger' => 50,
            'availability_trigger' => true,
        ]);
    }

    /** @test **/
    public function the_alert_channel_must_belong_to_the_logged_in_user(): void
    {
        // Arrange
        $alertChannelA = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        $alertChannelB = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        Sanctum::actingAs($alertChannelA->user);

        // Act
        $response = $this->json('POST', route('trackingAlert.store'), [
            'alert_channel_uuid' => $alertChannelB->uuid,
            'percentage_trigger' => '50',
            'availability_trigger' => true,
        ]);

        // Assert
        $response->assertJsonValidationErrorFor('alert_channel_uuid');
    }

    /** @test **/
    public function if_an_alert_channel_requires_verification_but_has_not_been_verified_it_may_not_be_attached_to_a_tracking_alert(): void
    {
        // Arrange
        $alertChannel = AlertChannel::factory()->verificationRequiredChannel()->create();
        Sanctum::actingAs($alertChannel->user);

        // Act
        $response = $this->json('POST', route('trackingAlert.store'), [
            'alert_channel_uuid' => $alertChannel->uuid,
            'percentage_trigger' => '50',
            'availability_trigger' => true,
        ]);

        // Assert
        $response->assertJsonValidationErrorFor('alert_channel_uuid');
    }

    /** @test **/
    public function the_sanctum_middleware_is_attached(): void
    {
        $this->assertRouteUsesMiddleware('trackingAlert.store', ['auth:sanctum']);
    }

    /**
     * @test
     * @dataProvider validationProvider
     **/
    public function validation_tests(array $payload, string $error): void
    {
        // Arrange
        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        Sanctum::actingAs($alertChannel->user);

        // Act
        $response = $this->json('POST', route('trackingAlert.store'), array_merge($payload, [
            'alert_channel_uuid' => $alertChannel->uuid,
        ]));

        // Assert

        $response->assertJsonValidationErrorFor($error);
        $this->assertDatabaseCount('tracking_alerts', 0);
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
            'missing_both' => [
                'payload' => Arr::except($good, ['percentage_trigger', 'availability_trigger']),
                'error' => 'percentage_trigger',
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
