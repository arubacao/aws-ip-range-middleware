<?php

namespace Arubacao\AwsIpRange\Test;

use Arubacao\AwsIpRange\AwsIpRangeMiddleware;
use Illuminate\Support\Facades\Cache;

class AwsIpRangeMiddlewareTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->app->router->post('api/sns', ['middleware' => AwsIpRangeMiddleware::class, function () {
            return 'response';
        }]);
    }

    /** @test */
    public function it_returns_403_non_valid_ip()
    {
        $response = $this->call('POST', 'api/sns');
        if (is_a($response, 'Illuminate\Foundation\Testing\TestResponse') ||
            is_a($response, 'Illuminate\Testing\TestResponse')) {
            $response->assertStatus(403);
        } else {
            $this->assertResponseStatus(403);
        }
    }

    /** @test */
    public function it_caches_aws_ip_ranges()
    {
        $this->assertFalse(Cache::has(AwsIpRangeMiddleware::CACHE_KEY));
        $response = $this->call('POST', 'api/sns');
        $this->assertTrue(Cache::has(AwsIpRangeMiddleware::CACHE_KEY));

        $cache = Cache::get(AwsIpRangeMiddleware::CACHE_KEY);
        $download = json_decode(file_get_contents(AwsIpRangeMiddleware::URL), true);

        $this->assertContains($download['prefixes'][0]['ip_prefix'], $cache);
        $this->assertContains($download['prefixes'][count($download['prefixes']) - 1]['ip_prefix'], $cache);
        $this->assertContains($download['ipv6_prefixes'][0]['ipv6_prefix'], $cache);
        $this->assertContains($download['ipv6_prefixes'][count($download['ipv6_prefixes']) - 1]['ipv6_prefix'], $cache);
    }

    /** @test */
    public function it_passes_valid_ips()
    {
        foreach ($this->getValidIpAwsAddresses() as $ip) {
            $response = $this->call('POST', 'api/sns', [], [], [], ['REMOTE_ADDR' => $ip]);

            if (is_a($response, 'Illuminate\Foundation\Testing\TestResponse') ||
                is_a($response, 'Illuminate\Testing\TestResponse')) {
                $response->assertStatus(200);
            } else {
                $this->assertResponseStatus(200);
            }
        }
    }

    /**
     * An array of valid Aws ip addresses.
     * @return array
     */
    private function getValidIpAwsAddresses()
    {
        return [
            '52.94.196.1',
            '52.94.196.254',
            '52.94.198.145',
            '52.94.198.158',
            '46.51.192.1',
            '46.51.207.254',
            '207.171.167.101',
            '207.171.167.25',
            '207.171.167.26',
            '207.171.172.6',
            '2a05:d050:c000:0:0:0:0:0',
            '2a05:d050:c0ff:ffff:ffff:ffff:ffff:ffff',
            '2a05:d07c:8000:0:0:0:0:0',
            '2a05:d07c:80ff:ffff:ffff:ffff:ffff:ffff',
        ];
    }
}
