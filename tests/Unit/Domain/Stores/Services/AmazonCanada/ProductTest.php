<?php

namespace Tests\Unit\Domain\Stores\Services\AmazonCanada;

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use GuzzleHttp\Psr7\Uri;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /** @test **/
    public function it_fetches_a_product_from_amazon_using_a_url(): void
    {
        // Arrange
        $url = new Uri('https://www.amazon.ca/all-new-fire-tv-stick-4k-with-alexa-voice-remote/dp/B08XVW2CRY/?_encoding=UTF8&pd_rd_w=Sa4V8&content-id=amzn1.sym.65a883cc-2a99-4757-97c3-17282bb2b972&pf_rd_p=65a883cc-2a99-4757-97c3-17282bb2b972&pf_rd_r=Z9B6XWFKK662R6SFG8JS&pd_rd_wg=A8FdG&pd_rd_r=76d1bfc2-97e2-458f-9546-40a8618c5cbc&ref_=pd_gw_ci_mcx_mr_hp_atf_m');

        // Act
        $amazonService = app(AmazonCanadaService::class);

        // Assert

        dd(
            $amazonService->search('3080')
        );
    }

    // TODO: Command to create a snapshot of the page html and image every week and save it into a directory
    // TODO: Test will run a partial mock of spatie/browsershot using those snapshots to save time
    // TODO: Testing controllers and commands will then rely on a full mock/stub of the service class since testing it again will eb redundant
}
