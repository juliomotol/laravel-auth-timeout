# Laravel Auth Timeout

[![Latest Version on Packagist](https://img.shields.io/packagist/v/juliomotol/laravel-auth-timeout.svg?style=flat-square)](https://packagist.org/packages/juliomotol/laravel-auth-timeout)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/juliomotol/laravel-auth-timeout/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/juliomotol/laravel-auth-timeout/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/juliomotol/laravel-auth-timeout/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/juliomotol/laravel-auth-timeout/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/juliomotol/laravel-auth-timeout.svg?style=flat-square)](https://packagist.org/packages/juliomotol/laravel-auth-timeout)

Handle Authentication timeouts in Laravel.

> When upgrading to v4, please see the [CHANGELOG.md](./CHANGELOG.md).

> For Laravel 8+ support, see [v3](https://github.com/juliomotol/laravel-auth-timeout/tree/v3).
>
> For Laravel 6+ support, see [v2](https://github.com/juliomotol/laravel-auth-timeout/tree/v2).

## Why Laravel Auth Timeout?

There are times where we want to log out a user when they haven't done any request within a set time. There is a workaround (below):

```
/* Somewhere in config/session.php */
'lifetime' => 15,
```

But this affects the entirety of the session. But it doesnt have to be and that is where Laravel Auth Timeout comes in.

Laravel Auth Timeout is a small middleware package that checks if the user had made any request in a set of time. If they have reached the idle time limit, they are then logged out on their next request. Thanks to Brian Matovu's [article](http://bmatovu.com/laravel-session-timeout-auto-logout/).

## Installation

You can install the package via composer:

```sh
composer require juliomotol/laravel-auth-timeout
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-auth-timeout-config"
```

This is the contents of the published config file:

```php
<?php

return [

    /**
     * The session name used to identify if the user has reached the timeout time.
     */
    'session' => 'last_activity_time',


    /**
     * The minutes of idle time before the user is logged out.
     */
    'timeout' => 15,

    /**
     * The event that will be dispatched when a user has timed out.
     */
    'event' => JulioMotol\AuthTimeout\Events\AuthTimedOut::class,

];
```

## Usage

### Quick Start

For a simple usage, register the `CheckAuthTimeout` in your `Kernel.php`.

```php
protected $routeMiddleware = [
    ...
    'auth.timeout' => \JulioMotol\AuthTimeout\Middlewares\CheckAuthTimeout::class,
    ...
];
```

Then use that middleware on a route.

```php
Route::get('/admin', [
    'uses' => 'FooBarController@Foobar',
    'middleware' => ['auth.timeout']
]);
```

### Using Different Guards

You might have multiple guards and only want to apply `CheckAuthTimeout` to certain ones. We got you covered, `CheckAuthTimeout` accepts a `$guard` parameter.

```php
Route::get('/admin', [
    'uses' => 'FooBarController@Foobar',
    'middleware' => ['auth.timeout:custom-guard'] // Add the guard name as a parameter for the auth.timeout middleware.
]);
```

> NOTE: This package only works with guards that uses a `session` driver.

### AuthTimedOut

An `AuthTimedOut` will be dispatch every time a user has timed out. You can assign a listener for this event in your `EventServiceProvider`.

```php
protected $listen = [
    \JulioMotol\AuthTimeout\Events\AuthTimedOut::class => [
        // ...
    ],
];
```

`AuthTimedOut` has two properties that you can access in your `EventListener`.

```php
class FooEventListener
{
    public function handle(AuthTimedOut $event)
    {
        $event->user;
        $event->guard;
    }
}
```

### Redirection

To modify the redirection when a user has timed out, you can use `CheckAuthTimeout::setRedirectTo()` within your `AppServiceProvider` to set a redirection callback.

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        CheckAuthTimeout::setRedirectTo(function ($request, $guard){
            return match($guard){
                'custom-guard' => route('some.route'),
                default => route('auth.login')
            }
        });
    }
}
```

### AuthTimeout Facade

This package also provides a facade with the following methods:

```php
AuthTimeout::init() // Initialize the timeout session when no has been set yet.

AuthTimeout::check($guard) // Check if a user has timed out and logs them out if so.

AuthTimeout::hit() // Reset the user's timeout session.

AuthTimeout::lastActiveAt() // The last activity time of the user.
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Julio Motol](https://github.com/juliomotol)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
