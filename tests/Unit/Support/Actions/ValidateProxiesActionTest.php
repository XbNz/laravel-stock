<?php

namespace Tests\Unit\Support\Actions;

use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Support\Actions\ValidateProxiesAction;
use Tests\TestCase;

class ValidateProxiesActionTest extends TestCase
{
    /**
     * @test
     * @dataProvider proxyProvider
     **/
    public function it_returns_a_validated_list_of_proxies(string $proxy, bool $expectedToPass): void
    {
        // Arrange
        Config::set([
            'proxy.proxies' => [$proxy],
        ]);

        // Act
        if ($expectedToPass === false) {
            $this->expectException(\Webmozart\Assert\InvalidArgumentException::class);
        }

        $collectionOfProxies = app(ValidateProxiesAction::class)();


        // Assert
        $this->assertInstanceOf(Collection::class, $collectionOfProxies);
    }

    public function proxyProvider(): Generator
    {
        yield from [
            'normal_http_proxy' => [
                'proxy' => 'http://127.0.0.1:8890',
                'expectedToPass' => true,
            ],
            'normal_https_proxy' => [
                'proxy' => 'https://127.0.0.1:8890',
                'expectedToPass' => true,
            ],
            'normal_socks4_proxy' => [
                'proxy' => 'socks4://127.0.0.1:8890',
                'expectedToPass' => true,
            ],
            'normal_socks4a_proxy' => [
                'proxy' => 'socks4a://127.0.0.1:8890',
                'expectedToPass' => true,
            ],
            'normal_socks5_proxy' => [
                'proxy' => 'socks5://127.0.0.1:8890',
                'expectedToPass' => true,
            ],
            'normal_socks5h_proxy' => [
                'proxy' => 'socks5h://127.0.0.1:8890',
                'expectedToPass' => true,
            ],
            'normal_http_proxy_with_username_and_password' => [
                'proxy' => 'http://username:password@127.0.0.1:8890',
                'expectedToPass' => false,
            ],
            'incorrect_port' => [
                'proxy' => 'http://127.0.0.1:65536',
                'expectedToPass' => false,
            ],
            'incorrect_port_2' => [
                'proxy' => 'http://127.0.0.1:-1',
                'expectedToPass' => false,
            ],
            'port_not_present' => [
                'proxy' => 'http://127.0.0.1',
                'expectedToPass' => false,
            ],
            'unsupported_protocol' => [
                'proxy' => 'ftp://127.0.0.1:8890',
                'expectedToPass' => false,
            ],
        ];
    }
}
