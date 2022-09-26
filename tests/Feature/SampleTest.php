<?php

namespace Tests\Feature;

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use Domain\Users\Models\User;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use Tests\TestCase;

class SampleTest extends TestCase
{
    use RefreshDatabase;

    public function testBasic()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('POST', route('trackingRequest.store'), [
            'url' => 'https://www.bestbuy.ca/en-ca/product/kalorik-pro-digital-air-fryer-3-3kg-3-5qt/16366443',
            'update_interval' => 60,
        ]);

        dd(\DB::table('stocks')->get());

    }
}
