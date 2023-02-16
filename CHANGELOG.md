# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## v4.0.0 (2022-02-16)

Laravel Auth Timeout has received a complete refresh. Please make sure to read through all the changes.

## Added

- Support for Laravel 10.x.
- Added `event` in config.

## Changed

- Deprecated `JulioMotol\AuthTimeout\Contracts\AuthTimeout`'s `reset()` method and will be remove on a future release. Use `hit()` instead.
    - The same change applies to `JulioMotol\AuthTimeout\Facade\AuthTimeout` and `JulioMotol\AuthTimeout\Facade\AuthTimeout`
- Renamed ~~`JulioMotol\AuthTimeout\Events\AuthTimeoutEvent`~~ to `JulioMotol\AuthTimeout\Events\AuthTimedOut`.
- Renamed ~~`JulioMotol\AuthTimeout\Middleware\AuthTimeoutMiddleware`~~ to `JulioMotol\AuthTimeout\Middlewares\CheckAuthTimeout`.
- Auth Timeout initialization has been moved by listening to the `Illuminate\Auth\Events\Login`.
    - This was previously initialized within the `JulioMotol\AuthTimeout\Middleware\AuthTimeoutMiddleware`

## v3.1.1 (2022-06-03)

### Fixed

-   Fix compatibility with v2 session timeout storing. [#24](https://github.com/juliomotol/laravel-auth-timeout/issues/24)

## v3.1.0 (2022-04-18)

### Added

-   Add `AuthTimeoutMiddleware::setRedirectTo()` method.
-   Support for Laravel 9.x

### Changed

-   Update syntax to PHP8.0.

### Removed

-   Remove support for PHP7.4 and lower.

## v3.0.1 (2021-01-09)

### Added

-   Restore PHP 7.3 support.

## v3.0.0 (2021-01-08)

### Added

-   PHP 8 Support.

### Changed

-   AuthTimeout now uses `carbon\carbon` to check and store timeout sessions.

### Removed

-   Removed `auth-timeout.redirect` in favor of `AuthTimeoutMiddleware::redirectTo()`.
    -   If you are using this config key, we highly suggest to use `AuthTimeoutMiddleware::redirectTo()` as it provides much better flexibility.

## v2.2.1 (2020-10-12)

-   Fixed `AuthTimeout` facade.

## v2.2.0 (2020-10-12)

-   Added `AuthTimeout` facade.

## v2.1.0 (2020-09-11)

-   Added support for Laravel 8.x.

## v2.0.0 (2020-08-03)

### Breaking Changes

-   The `AuthTimeoutMiddleware` class' `redirectTo()` method signature has changed. If you are overriding this method, you should update your method's signature:

```php
/**
 * Get the path the user should be redirected to when they timed out.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  mixed    $guard
 *
 * @return string|null
 */
protected function redirectTo($request, $guard = null)
{
    //
}
```

### Features

-   The `AuthTimeoutEvent` class now has a `$guard` property.

## v1.0.0 (2020-04-04) First Release!

Welcome to Laravel Auth Timeout!

See the [documentation](./README.md) to get started.
