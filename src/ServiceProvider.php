<?php

namespace JulioMotol\AuthTimeout;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JulioMotol\AuthTimeout\Contracts\AuthTimeout as AuthTimeoutContract;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/auth-timeout.php' => config_path('auth-timeout.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auth-timeout.php', 'auth-timeout');

        $this->app->singleton(AuthTimeoutContract::class, function ($app) {
            return new AuthTimeout($app['auth'], $app['events'], $app['session']);
        });

        $this->app->alias(AuthTimeoutContract::class, 'AuthTimeout');
    }
}
