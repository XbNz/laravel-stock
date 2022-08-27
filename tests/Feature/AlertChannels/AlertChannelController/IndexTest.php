<?php

declare(strict_types=1);

namespace Tests\Feature\AlertChannels\AlertChannelController;

use Domain\Alerts\Models\AlertChannel;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_may_retrieve_only_their_own_alert_channels(): void
    {
        // Arrange
        $channelA = AlertChannel::factory(1, [
            'type' => 'email',
            'value' => '::anything::',
        ]);
        $userA = User::factory()->has($channelA)->create();

        $channelB = AlertChannel::factory(1, [
            'type' => 'email',
            'value' => '::anythingelse::',
        ]);
        $userB = User::factory()->has($channelB)->create();

        Sanctum::actingAs($userA);

        // Act
        $response = $this->json('GET', route('alertChannel.index'));

        // Assert

        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment([
            'value' => '::anything::',
        ]);

        $response->assertJsonMissing([
            'value' => '::anythingelse::',
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid',
                    'value',
                    'type',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('alertChannel.index', ['auth:sanctum']);
    }
}
