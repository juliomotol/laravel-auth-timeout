# Changelog

## v2.1.0 (2020-09-11)

-   Added support for Laravel 8.x

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
