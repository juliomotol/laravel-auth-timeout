<?php

namespace JulioMotol\AuthTimeout;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use JulioMotol\AuthTimeout\Contracts\AuthTimeout as AuthTimeoutContract;
use JulioMotol\AuthTimeout\Listeners\InitializeAuthTimeout;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AuthTimeoutServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-auth-timeout')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AuthTimeoutContract::class, function ($app) {
            return new AuthTimeout($app['auth'], $app['events'], $app['session.store']);
        });

        $this->booting(fn () => Event::listen(Login::class, InitializeAuthTimeout::class));
    }
}
