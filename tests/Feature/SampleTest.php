<?php

declare(strict_types=1);

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
            'value' => 'https://discord.com/api/webhooks/1035052623251906560/LY_bmUEkXoEjIqRKEtVxRy0Ar7f3U4OiHsuc-j9Fjt6h_MzhJ12ip4EazQ_-22Psy3_Q',
        ]);

        $stockHistory = StockHistory::factory()->create([
            'availability' => true,
            'price' => 1877,
        ]);

        $stockHistoryB = StockHistory::factory()->create([
            'availability' => true,
            'price' => 1099,
        ]);

        $stockHistoryB->stock->update([
            'image' => storage_path('app/tmp/amazon_HEjzHfkazs.png'),
        ]);

        // Act
        $alertChannel->notify(new StockAvailabilityNotification($stockHistory, $stockHistoryB));

        // Assert
    }
}
