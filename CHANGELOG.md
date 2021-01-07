# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
## v3.0.0 (2021-01-08)
### Added
-   PHP 8 Support.

### Changed
-   AuthTimeout now uses `carbon\carbon` to check and store timeout sessions.

### Removed
-   Removed `auth-timeout.redirect` in favor of `AuthTimeoutMiddleware::redirectTo()`.
    - If you are using this config key, we highly suggest to use `AuthTimeoutMiddleware::redirectTo()` as it provides much better flexibility.

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
