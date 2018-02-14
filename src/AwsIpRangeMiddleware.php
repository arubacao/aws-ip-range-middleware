<?php

namespace Arubacao\AwsIpRange;

use Closure;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\IpUtils;
use GrahamCampbell\GuzzleFactory\GuzzleFactory;

class AwsIpRangeMiddleware
{
    const CACHE_KEY = 'arubacao_aws-ip-ranges';

    const URL = 'https://ip-ranges.amazonaws.com/ip-ranges.json';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
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
     * @return array
     */
    private function getAwsIpRanges(): array
    {
        return Cache::remember(self::CACHE_KEY, (new \DateTime())->modify('+1 day'), function () {
            return $this->mergeRanges($this->fetchData());
        });
    }

    /**
     * Fetch ip-ranges from aws.
     *
     * @return array
     */
    private function fetchData(): array
    {
        $client = GuzzleFactory::make([]);
        $response = $client->get(self::URL);
        $json = $response->getBody()->getContents();

        return \GuzzleHttp\json_decode($json, true);
    }

    /**
     * Merge ipv4 & ipv6.
     *
     * @param $array
     * @return array
     */
    private function mergeRanges($array): array
    {
        $ipRanges = array_column($array['prefixes'], 'ip_prefix');
        $ipV6Ranges = array_column($array['ipv6_prefixes'], 'ipv6_prefix');

        return array_merge($ipRanges, $ipV6Ranges);
    }
}
