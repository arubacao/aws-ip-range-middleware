<?php

namespace Arubacao\AwsIpRange\Test;

use Arubacao\AwsIpRange\AwsIpRangeMiddleware;
use Arubacao\AwsIpRange\AwsIpRangeServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;

class AwsIpRangeMiddlewareTest extends TestCase
{
    /** @var string */
    private $fixture;

    protected function getPackageProviders($app)
    {
        return [AwsIpRangeServiceProvider::class];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = file_get_contents(__DIR__.'/fixtures/ip-ranges.json');

        $mock = new MockHandler([
            new Response(200, [], $this->fixture),
            new Response(200, [], $this->fixture),
            new Response(200, [], $this->fixture),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->app->instance(Client::class, new Client(['handler' => $handlerStack]));

        $this->app->router->post('api/sns', ['middleware' => AwsIpRangeMiddleware::class, function () {
            return 'response';
        }]);
    }

    public function test_it_returns_403_non_valid_ip()
    {
        $response = $this->call('POST', 'api/sns');
        if (is_a($response, 'Illuminate\Foundation\Testing\TestResponse') ||
            is_a($response, 'Illuminate\Testing\TestResponse')) {
            $response->assertStatus(403);
        } else {
            $this->assertResponseStatus(403);
        }
    }

    public function test_it_caches_aws_ip_ranges()
    {
        $this->assertFalse(Cache::has(AwsIpRangeMiddleware::CACHE_KEY));
        $this->call('POST', 'api/sns');
        $this->assertTrue(Cache::has(AwsIpRangeMiddleware::CACHE_KEY));

        $cache = Cache::get(AwsIpRangeMiddleware::CACHE_KEY);
        $payload = json_decode($this->fixture, true);

        $this->assertContains($payload['prefixes'][0]['ip_prefix'], $cache);
        $this->assertContains($payload['prefixes'][count($payload['prefixes']) - 1]['ip_prefix'], $cache);
        $this->assertContains($payload['ipv6_prefixes'][0]['ipv6_prefix'], $cache);
        $this->assertContains($payload['ipv6_prefixes'][count($payload['ipv6_prefixes']) - 1]['ipv6_prefix'], $cache);
    }

    public function test_it_uses_configured_cache_key()
    {
        config(['aws-ip-range.cache_key' => 'aws-ip-range-test-key']);

        $this->assertFalse(Cache::has(AwsIpRangeMiddleware::CACHE_KEY));
        $this->assertFalse(Cache::has('aws-ip-range-test-key'));

        $this->call('POST', 'api/sns');

        $this->assertFalse(Cache::has(AwsIpRangeMiddleware::CACHE_KEY));
        $this->assertTrue(Cache::has('aws-ip-range-test-key'));
    }

    public function test_it_respects_zero_cache_ttl()
    {
        config([
            'aws-ip-range.cache_key' => 'aws-ip-range-zero-ttl',
            'aws-ip-range.cache_ttl' => 0,
        ]);

        $this->call('POST', 'api/sns');

        $this->assertFalse(Cache::has('aws-ip-range-zero-ttl'));
    }

    public function test_it_passes_valid_ips()
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

    public function test_it_publishes_config()
    {
        $configPath = config_path('aws-ip-range.php');

        if (file_exists($configPath)) {
            unlink($configPath);
        }

        $this->artisan('vendor:publish', [
            '--provider' => AwsIpRangeServiceProvider::class,
            '--tag' => 'config',
        ]);

        $this->assertFileExists($configPath);

        unlink($configPath);
    }

    /**
     * An array of valid Aws ip addresses.
     *
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
