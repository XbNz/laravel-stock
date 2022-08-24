<?php

declare(strict_types=1);

namespace Tests\Unit;

use Domain\Alerts\Actions\SignedUrlForChannelVerificationAction;
use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Notifications\VerifyAlertChannelNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function testThatTrueIsTrue()
    {
        $channel = AlertChannel::factory()->create(['type' => \Domain\Alerts\Enums\AlertChannel::Email, 'value' => 'whateevr@cef.com']);
        $url = app(SignedUrlForChannelVerificationAction::class)($channel);
        $channel->notifyNow(new VerifyAlertChannelNotification($url));

        // TODO: Continue with tracking requests now that alert channels are up.
    }
}
