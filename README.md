# Laravel Auth Timeout

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/juliomotol/laravel-auth-timeout.svg?style=flat-square)](https://packagist.org/packages/juliomotol/laravel-auth-timeout)
[![Total Downloads](https://img.shields.io/packagist/dt/juliomotol/laravel-auth-timeout.svg?style=flat-square)](https://packagist.org/packages/juliomotol/laravel-auth-timeout)

A small Laravel 8+ package that handles Authentication Timeouts.

When upgrading to v3, please see the [CHANGELOG.md](./CHANGELOG.md).

> For Laravel 6+ support, see [v2](https://github.com/juliomotol/laravel-auth-timeout/tree/v2).

## Why Laravel Auth Timeout?

There are times where we want to log out a user when they haven't done and request in a set of time. There is a workaround (below):

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

## Config

| Key     | Default value          | Description                               |
| ------- | ---------------------- | ----------------------------------------- |
| session | `"last_activity_time"` | The name of the session token to be used. |
| timeout | `15`                   | The timeout duration in minutes.          |

> If you want to make changes in the configuration you can publish the config file using:
>
> ```sh
> php artisan vendor:publish --provider="JulioMotol\AuthTimeout\ServiceProvider"
> ```

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

> This package only works with guards that uses `session` as its driver

### AuthTimeoutEvent

An `AuthTimeoutEvent` will dispatch every time a user has timed out. You can assign a listener for this event in your `EventServiceProvider`.

```php
protected $listen = [
    \JulioMotol\AuthTimeout\Events\AuthTimeoutEvent::class => [
        // Your Listeners...
    ],
];
```

`AuthTimeoutEvent` has two properties that you can access in your `EventListener`.

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

To modify the redirection when a user has timed out, you can use `AuthTimeoutMiddleware::setRedirectTo()` within your `AppServiceProvider` to set a redirection callback.

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        AuthTimeoutMiddleware::setRedirectTo(function ($request, $guard){
            switch($guard){
                case 'web.admin':
                    return route('auth.admin.login');
                default:
                    return route('auth.login');
            }
        });
    }
}
```

### AuthTimeout Facade

This package also provides a facade with the following methods:

-   `AuthTimeout::init()` - Initialize the timeout session when no has been set yet.
-   `AuthTimeout::check($guard = null)` - Check if a user has timed out and logs them out if so.
-   `AuthTimeout::reset()` - Reset the user's timeout session.

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
