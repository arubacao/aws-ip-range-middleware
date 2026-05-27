<?php

declare(strict_types=1);

namespace Arubacao\AwsIpRange;

use Illuminate\Support\ServiceProvider;

class AwsIpRangeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/aws-ip-range.php',
            'aws-ip-range'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/aws-ip-range.php' => config_path('aws-ip-range.php'),
            ], 'config');
        }
    }
}
