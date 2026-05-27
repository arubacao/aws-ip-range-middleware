# Laravel Middleware for Amazon Web Services (AWS) IP Address Range Validation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arubacao/aws-ip-range-middleware.svg?style=flat-square)](https://packagist.org/packages/arubacao/aws-ip-range-middleware)
[![Run Tests](https://github.com/arubacao/aws-ip-range-middleware/workflows/Run%20Tests/badge.svg)](https://github.com/arubacao/aws-ip-range-middleware/actions?query=workflow%3A%22Run+Tests%22)
[![Total Downloads](https://img.shields.io/packagist/dt/arubacao/aws-ip-range-middleware.svg?style=flat-square)](https://packagist.org/packages/arubacao/aws-ip-range-middleware)

This package allows for **validation** of incoming **requests** against the official [Amazon Web Services (AWS) IP Address Range](https://docs.aws.amazon.com/general/latest/gr/aws-ip-ranges.html).
Use this to determine if an incoming request actually comes from the AWS infrastructure e.g. for [Simple Notification Service (SNS)](https://docs.aws.amazon.com/sns/latest/dg/welcome.html) payloads.

## Features
 - Passes incoming HTTP requests from AWS, rejects everything else with a `403`.
 - AWS *IP address range* is fetched on demand and therefore always up-to-date.
 - Caches the parsed *IP address range* (default: 24 hours).
 - Source URL, cache key, and TTL are configurable.

## Supported versions

Actively tested in CI:

| PHP   | Laravel      |
|-------|--------------|
| `8.1` | `9.*`, `10.*` |
| `8.2` | `10.*`, `11.*` |
| `8.3` | `11.*`, `12.*` |
| `8.4` | `11.*`, `12.*` |

This package requires PHP `8.1+` and Laravel `9+`.

## Installation

Install this package via composer:

```bash
composer require arubacao/aws-ip-range-middleware
```

Laravel registers the service provider automatically via package discovery. If package discovery is disabled, add the provider manually in `config/app.php`:

```php
'providers' => [
    // ...
    Arubacao\AwsIpRange\AwsIpRangeServiceProvider::class,
],
```

### Registering the middleware

Assign the middleware a key in your `app/Http/Kernel.php`:

```php
// Within App\Http\Kernel...

protected $routeMiddleware = [
    // ...
    'aws-ip-range' => \Arubacao\AwsIpRange\AwsIpRangeMiddleware::class,
];
```

## Usage

```php
Route::post('api/sns', function () {
    //
})->middleware('aws-ip-range');
```

You can also pass the fully qualified class name (no Kernel registration required):

```php
use Arubacao\AwsIpRange\AwsIpRangeMiddleware;

Route::post('api/sns', function () {
    //
})->middleware(AwsIpRangeMiddleware::class);
```

## Configuration

Publish the config file to override the defaults:

```bash
php artisan vendor:publish --provider="Arubacao\AwsIpRange\AwsIpRangeServiceProvider" --tag=config
```

This creates `config/aws-ip-range.php`:

```php
return [
    'url'       => env('AWS_IP_RANGE_URL', 'https://ip-ranges.amazonaws.com/ip-ranges.json'),
    'cache_key' => env('AWS_IP_RANGE_CACHE_KEY', 'arubacao_aws-ip-ranges'),
    'cache_ttl' => (int) env('AWS_IP_RANGE_CACHE_TTL', 86400),
];
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Christopher Lass](https://github.com/arubacao)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
