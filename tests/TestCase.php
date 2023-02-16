<?php

namespace JulioMotol\AuthTimeout\Tests;

use JulioMotol\AuthTimeout\AuthTimeoutServiceProvider;
use JulioMotol\AuthTimeout\Facades\AuthTimeout;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            AuthTimeoutServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'AuthTimeout' => AuthTimeout::class,
        ];
    }
}
