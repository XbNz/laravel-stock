<?php

namespace Tests\Feature;

use Domain\Alerts\Models\AlertChannel;
use Domain\Stocks\Models\StockHistory;
use Domain\Stocks\Notifications\StockAvailabilityNotification;
use Domain\Stocks\Notifications\StockPriceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function example(): void
    {
        // Arrange
        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create([
            'type' => \Domain\Alerts\Enums\AlertChannel::Discord,
            'value' => 'https://discord.com/api/webhooks/1028017034736443422/UAkUCa41bZf0SHG4OKKF9SALa0nl3vYjmpE-ccpZ2pIyFUnY0145odfGDcjmWUTmENPo',
        ]);

        $stockHistory = StockHistory::factory()->create([
            'availability' => true,
            'price' => 1877,
        ]);

        $stockHistoryB = StockHistory::factory()->create([
            'availability' => true,
            'price' => 1099,
        ]);

        // Act
        $alertChannel->notify(new StockPriceNotification($stockHistory, $stockHistoryB));

        // Assert
    }
}
