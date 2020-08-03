# Laravel Auth Timeout

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![StyleCI](https://github.styleci.io/repos/252781961/shield?branch=master)](https://github.styleci.io/repos/252781961)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/juliomotol/laravel-auth-timeout.svg?style=flat-square)](https://packagist.org/packages/juliomotol/laravel-auth-timeout)
[![Total Downloads](https://img.shields.io/packagist/dt/juliomotol/laravel-auth-timeout.svg?style=flat-square)](https://packagist.org/packages/juliomotol/laravel-auth-timeout)

A small Laravel 6+ package that handles Authentication Timeouts.

When upgrading to v2, please see the [CHANGELOG.md](./CHANGELOG.md).

## Why Laravel Auth Timeout?

There are times where we want to log out a user when they haven't done and request in a set of time. There is a workaround (below):
```
/* somewhere in config/session.php */
'lifetime' => 15,
```
But this also affects the CSRF token and we don't want that. That is where Laravel Auth Timeout comes in.

Laravel Auth Timeout is a small middleware package that checks if the user had made any request in a set of time. If they have reached the idle time limit, they are then logged out. Thanks to Brian Matovu's [article](http://bmatovu.com/laravel-session-timeout-auto-logout/).

## Installation

```sh
composer require juliomotol/laravel-auth-timeout
```

This package uses [auto-discovery](https://laravel.com/docs/5.5/packages#package-discovery), so you don't have to do anything. It works out of the box.

## Config

If you want to make changes in the configuration you can publish the config file using:

```sh
php artisan vendor:publish --provider="JulioMotol\AuthTimeout\ServiceProvider"
```

### Content of the configuration

| Key      | Default value          | Description                                                                                          |
| -------- | ---------------------- | ---------------------------------------------------------------------------------------------------- |
| session  | `"last_activity_time"` | The name of the session token to be used.                                                            |
| timeout  | `15`                   | The timeout duration in minutes.                                                                     |
| redirect | `null`                 | The path to redirect the user when timed out. (For more flexibilty, see [Redirection](#redirection)) |

## Usage

### Quick Start

For a simple usage, include the `AuthTimeoutMiddleware` in your `Kernel.php` and use that middleware on the route you want this to take effect in.

```php
/* Kernel.php */

protected $routeMiddleware = [
    ...
    'auth.timeout' => \JulioMotol\AuthTimeout\Middleware\AuthTimeoutMiddleware::class,
    ...
];

/* Routes.php */
Route::get('/admin', [
    'uses' => 'FooBarController@Foobar',
    'middleware' => ['auth.timeout']
]);
```

As simple as that.

### Custom Guards

You might have multiple guards and only want to apply `AuthTimeoutMiddleware` to certain ones. We got you covered, `AuthTimeoutMiddleware` accepts a `$guard` as its parameter.

```php
// Lets say you have added a 'web.admin' guard in your config/auth.php...

/* Routes.php */
Route::get('/admin', [
    'uses' => 'FooBarController@Foobar',
    'middleware' => ['auth.timeout:web.admin'] // Add the guard name as a parameter for the auth.timeout middleware.
]);
```

> NOTE: This package only works with guards that uses "session" as its driver

### AuthTimeoutEvent

The `AuthTimeoutMiddleware` also dispatches an `AuthTimeoutEvent` every time a user has timed out. You can assign a listener for this event in your `EventServiceProvider`

```php
protected $listen = [
    \JulioMotol\AuthTimeout\Events\AuthTimeoutEvent::class => [
        // Your Listeners...
    ],
];
```

`AuthTimeoutEvent` has two properties that you can access in your `EventListener`

```php
class FooEventListener
{
    public function handle(AuthTimeoutEvent $event)
    {        
        $event->user;   // The user that timed out.
        $event->guard;  // The authentication guard.
    }
}
```

### Redirection

You might find the `redirect` option in the config a bit less flexible. You can extend the `AuthTimeoutMiddleware` then override the `redirectTo()` method.

```php
<?php

namespace App\Http\Middleware;

use JulioMotol\AuthTimeout\Middleware\AuthTimeoutMiddleware as BaseMiddleware;

class AuthTimeoutMiddleware extends BaseMiddleware
{
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
}
```

If you have multiple guards, you can use the `$guard` parameter to determine how and where a certain guard should be redirected.

## Contributing

Contributions are **welcome** and will be fully **credited**. We accept contributions via Pull Requests on [Github](https://github.com/juliomotol/larvel-auth-timeout).

Please read and understand the contribution guide before creating an issue or pull request.

### Pull Requests

Before submitting a pull request:

-   Make sure to write tests!
-   Document any change in behaviour. Make sure the `README.md` and any other relevant documentation are kept up-to-date.
-   One pull request per feature. If you want to do more than one thing, send multiple pull requests.

## License

This project and the Laravel framework are open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
