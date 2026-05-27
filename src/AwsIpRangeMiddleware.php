<?php

declare(strict_types=1);

namespace Arubacao\AwsIpRange;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\IpUtils;
use Throwable;

class AwsIpRangeMiddleware
{
    const CACHE_KEY = 'arubacao_aws-ip-ranges';

    const URL = 'https://ip-ranges.amazonaws.com/ip-ranges.json';

    const CACHE_TTL = 86400;

    /**
     * @var Client|null
     */
    private $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! IpUtils::checkIp($request->ip(), $this->getAwsIpRanges())) {
            return response('', 403);
        }

        return $next($request);
    }

    /**
     * @return array<int, string>
     */
    private function getAwsIpRanges(): array
    {
        $key = config('aws-ip-range.cache_key') ?: self::CACHE_KEY;
        $ttlSeconds = config('aws-ip-range.cache_ttl', self::CACHE_TTL);
        $ttlSeconds = $ttlSeconds === null ? self::CACHE_TTL : (int) $ttlSeconds;

        return Cache::remember($key, $ttlSeconds, function () {
            return $this->mergeRanges($this->fetchData());
        });
    }

    /**
     * Fetch ip-ranges from aws.
     *
     * @return array<string, mixed>
     */
    private function fetchData(): array
    {
        $url = config('aws-ip-range.url') ?: self::URL;

        try {
            $client = $this->client ?: new Client;
            $response = $client->request('GET', $url);
            $json = (string) $response->getBody();
            $data = json_decode($json, true);

            if (! is_array($data)) {
                throw new \RuntimeException('AWS ip-ranges response was not valid JSON.');
            }

            return $data;
        } catch (Throwable $e) {
            Log::warning('Failed to fetch AWS IP ranges.', ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Merge ipv4 & ipv6.
     *
     * @param  array<string, mixed>  $array
     * @return array<int, string>
     */
    private function mergeRanges(array $array): array
    {
        $ipRanges = array_column($array['prefixes'] ?? [], 'ip_prefix');
        $ipV6Ranges = array_column($array['ipv6_prefixes'] ?? [], 'ipv6_prefix');

        return array_merge($ipRanges, $ipV6Ranges);
    }
}
