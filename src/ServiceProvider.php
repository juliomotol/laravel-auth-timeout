<?php

namespace JulioMotol\AuthTimeout;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/auth-timeout' => config_path('auth-timeout'),
        ]);
    }
}
