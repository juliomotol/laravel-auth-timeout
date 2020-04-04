<?php

namespace JulioMotol\AuthTimeout;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

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
    }
}
