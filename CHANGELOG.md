# Changelog

All notable changes to `arubacao/aws-ip-range-middleware` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-05-27

### Added
- Support for Laravel 9, 10, 11, and 12.
- Support for Symfony HttpFoundation 7.
- Publishable configuration file (`config/aws-ip-range.php`) for the source URL, cache key, and cache TTL, including matching `AWS_IP_RANGE_*` env vars.
- `AwsIpRangeServiceProvider` with Laravel auto-discovery and config publishing.
- PHPStan + Larastan static analysis at level 6 (`composer analyse`).
- Laravel Pint for code style (`composer style` / `composer style:fix`).
- CI matrix for supported PHP 8.1-8.4 and Laravel 9-12 combinations.

### Changed
- Minimum PHP raised from `7.0` to `8.1`.
- Minimum Laravel/Illuminate support raised from `5.0` to `9.0`.
- Replaced abandoned `graham-campbell/guzzle-factory` with direct `guzzlehttp/guzzle ^7.0`.
- Replaced deprecated `\GuzzleHttp\json_decode()` with native `json_decode()`.
- Runtime files now declare `strict_types=1`.
- Network failures while fetching the AWS IP range list are now logged via `Log::warning(...)` before being rethrown (previously the exception propagated silently).
- GitHub Actions bumped to `actions/checkout@v4` and `actions/cache@v4`.
- `phpunit.xml.dist` trimmed to a minimal, version-agnostic schema (the previous schema used attributes removed in PHPUnit 10+).
- Test methods renamed from `it_does_x` (with `/** @test */` annotations) to `test_it_does_x` so they're discovered by PHPUnit 10+.
- Cache TTL configuration now preserves explicit `0` values instead of falling back to the default TTL.

### Removed
- Support for PHP 7.x, PHP 8.0, and Laravel 5-8.
- `.travis.yml` (long superseded by GitHub Actions).
- `.styleci.yml` (replaced by Laravel Pint).
- Inaccurate "Retry with exponential back-off" claim from the README; the feature was never implemented.

### Fixed
- Test suite no longer makes live HTTP calls to `ip-ranges.amazonaws.com`; uses a checked-in fixture and a Guzzle `MockHandler`.

## [1.1.0] - 2021-05-03

### Added
- Laravel 8 support.

[1.2.0]: https://github.com/arubacao/aws-ip-range-middleware/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/arubacao/aws-ip-range-middleware/releases/tag/v1.1.0
