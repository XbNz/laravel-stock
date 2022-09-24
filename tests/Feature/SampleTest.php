<?php

namespace Tests\Feature;

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use Tests\TestCase;

class SampleTest extends TestCase
{
    public function testBasic()
    {
        $service = app(AmazonCanadaService::class);

        $t = $service->search([
            new Uri('https://www.amazon.ca/s?k=iphone&i=electronics&crid=2CU5ICZOK15KX&sprefix=ipho%2Celectronics%2C385&ref=nb_sb_noss_2'),
        ]);

        dd($t);
    }
}
