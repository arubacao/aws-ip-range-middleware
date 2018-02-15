# Laravel Middleware for Amazon Web Services (AWS) IP Address Range Validation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arubacao/aws-ip-range-middleware.svg?style=flat-square)](https://packagist.org/packages/arubacao/aws-ip-range-middleware)
[![Build Status](https://img.shields.io/travis/arubacao/aws-ip-range-middleware/master.svg?style=flat-square)](https://travis-ci.org/arubacao/aws-ip-range-middleware)
[![Codecov](https://img.shields.io/codecov/c/github/arubacao/aws-ip-range-middleware.svg?style=flat-square)](https://codecov.io/gh/arubacao/aws-ip-range-middleware)
[![Total Downloads](https://img.shields.io/packagist/dt/arubacao/aws-ip-range-middleware.svg?style=flat-square)](https://packagist.org/packages/arubacao/aws-ip-range-middleware)

This package allows for **validation** of incoming **requests** against the official [Amazon Web Services (AWS) IP Address Range](https://docs.aws.amazon.com/general/latest/gr/aws-ip-ranges.html).  
Use this to determine if an incoming request actually comes from the AWS infrastructure e.g. for [Simple Notification Service (SNS)](https://docs.aws.amazon.com/sns/latest/dg/welcome.html) payloads.

## Features
 - Passes incoming HTTP requests from AWS, rejects everything else 
 - AWS _ip address range_ is fetched on demand and therefore always up-to-date
 - Caching of _ip address range_ --> only fetched once per day
 - Retry with exponential back-off on network issues while fetching the _ip address range_ from AWS 

#### Notes
 - `arubacao/aws-ip-range-middleware` is functional and fully tested for Laravel `5.0`, `5.1`, `5.2`, `5.3`, `5.4`, `5.5`, `5.6` and PHP `7.0`, `7.1`, `7.2`.
## Installation
Install this package via composer:

```bash
composer require arubacao/aws-ip-range-middleware
```

#### Registering Middleware

First assign the _aws-ip-range-middleware_ a key in your `app/Http/Kernel.php` file to the `$routeMiddleware` property.

```PHP
// Within App\Http\Kernel Class...

protected $routeMiddleware = [
    'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    // .
    // .
    // .
    'aws-ip-range' => \Arubacao\AwsIpRange\AwsIpRangeMiddleware::class,
];
```

## Usage

Once the _aws-ip-range-middleware_ has been defined in the HTTP kernel, you may use the middleware method to assign _aws-ip-range-middleware_ to a route:

```PHP
Route::post('api/sns', function () {
    //
})->middleware('aws-ip-range');


// Older Laravel Versions:
Route::post('api/sns', ['middleware' => 'aws-ip-range', function () {
    //
}]);
```

When assigning middleware, you may also pass the fully qualified class name:  
_Note: In this case you do not need to register the aws-ip-range-middleware in the HTTP kernel_  

```PHP
use Arubacao\AwsIpRange\AwsIpRangeMiddleware;

Route::post('api/sns', function () {
    //
})->middleware(AwsIpRangeMiddleware::class);


// Older Laravel Versions:
Route::post('api/sns', ['middleware' => AwsIpRangeMiddleware::class, function () {
    //
}]);
```


## Todo's

 - Enable/Disable caching
 - Choose cache storage
 - Command to fetch ip address range and store locally 

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Christopher Lass](https://github.com/arubacao)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
