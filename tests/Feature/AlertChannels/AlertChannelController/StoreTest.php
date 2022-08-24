<?php

namespace Tests\Feature\AlertChannels\AlertChannelController;

use Domain\Alerts\Enums\AlertChannel;
use Domain\Users\Models\User;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Propaganistas\LaravelPhone\PhoneNumber;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test **/
    public function a_logged_in_user_can_store_an_sms_alert(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->json('POST', route('alertChannel.store'), [
            'type' => AlertChannel::SMS->value,
            'value' => '+1 4164164166',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'type' => AlertChannel::SMS->value,
                'value' => PhoneNumber::make('+1 4164164166')->formatE164(),
            ]
        ]);

        $this->assertDatabaseCount('alert_channels', 1);
        $this->assertDatabaseHas('alert_channels', [
            'user_id' => $user->id,
            'type' => AlertChannel::SMS->value,
            'value' => PhoneNumber::make('+1 4164164166')->formatE164(),
            'verified_at' => null,
        ]);
    }

    /** @test **/
    public function a_logged_in_user_can_store_an_email_alert(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->json('POST', route('alertChannel.store'), [
            'type' => AlertChannel::Email->value,
            'value' => 'valid@gmail.com',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'type' => AlertChannel::Email->value,
                'value' => 'valid@gmail.com'
            ]
        ]);

        $this->assertDatabaseCount('alert_channels', 1);
        $this->assertDatabaseHas('alert_channels', [
            'user_id' => $user->id,
            'type' => AlertChannel::Email->value,
            'value' => 'valid@gmail.com',
            'verified_at' => null,
        ]);
    }

    /** @test **/
    public function a_logged_in_user_can_store_a_discord_alert(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->json('POST', route('alertChannel.store'), [
            'type' => AlertChannel::Discord->value,
            'value' => 'https://discord.com/api/webhooks/1010582812/5wwQgwQav6C-g4yfpG6Sz6kfde',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'type' => AlertChannel::Discord->value,
                'value' => 'https://discord.com/api/webhooks/1010582812/5wwQgwQav6C-g4yfpG6Sz6kfde',
            ]
        ]);

        $this->assertDatabaseCount('alert_channels', 1);
        $this->assertDatabaseHas('alert_channels', [
            'user_id' => $user->id,
            'type' => AlertChannel::Discord->value,
            'value' => 'https://discord.com/api/webhooks/1010582812/5wwQgwQav6C-g4yfpG6Sz6kfde',
            'verified_at' => null,
        ]);
    }

    /** @test **/
    public function the_alert_channel_type_and_value_must_be_unique(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $responseA = $this->json('POST', route('alertChannel.store'), [
            'type' => AlertChannel::Email->value,
            'value' => 'repetitive@gmail.com',
        ]);
        $responseB = $this->json('POST', route('alertChannel.store'), [
            'type' => AlertChannel::Email->value,
            'value' => 'repetitive@gmail.com',
        ]);

        // Assert
        $responseA->assertCreated();
        $responseB->assertJsonValidationErrorFor('type');
    }

    /** @test
     * @dataProvider validationProvider
     **/
    public function validation_tests(string $type, string $value, string $error): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->json('POST', route('alertChannel.store'), [
            'type' => $type,
            'value' => $value,
        ]);

        // Assert
        $response->assertJsonValidationErrorFor($error);
        $this->assertDatabaseCount('alert_channels', 0);
    }

    public function validationProvider(): Generator
    {
        yield from [
            'value_that_is_not_inside_of_enum' => [
                'type' => '::non-existent::',
                'value' => '::gibberish::',
                'error_field' => 'type',
            ],
            'totally_unacceptable_email_address' => [
                'type' => AlertChannel::Email->value,
                'value' => '::gibberish::',
                'error_field' => 'value',
            ],
            'unacceptable_email_address' => [
                'type' => AlertChannel::Email->value,
                'value' => 'surethislookslikeanemailbutisitreally@' . Str::random(10) . 'org',
                'error_field' => 'value',
            ],
            'totally_unacceptable_phone_number' => [
                'type' => AlertChannel::SMS->value,
                'value' => '::gibberish::',
                'error_field' => 'value',
            ],
            'missing_international_prefix' => [
                'type' => AlertChannel::SMS->value,
                'value' => '4164164166',
                'error_field' => 'value',
            ],
            'totally_unacceptable_discord_webhook' => [
                'type' => AlertChannel::Discord->value,
                'value' => '::gibberish::',
                'error_field' => 'value',
            ],
            'discord_webhook_with_another_domain' => [
                'type' => AlertChannel::Discord->value,
                'value' => 'https://notdiscord.com/api/webhooks/1010582812/5wwQgwQav6C-g4yfpG6Sz6kfde',
                'error_field' => 'value',
            ],
        ];
    }


}
