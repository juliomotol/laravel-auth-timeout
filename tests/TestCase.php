<?php

namespace JulioMotol\AuthTimeout\Tests;

use JulioMotol\AuthTimeout\Facades\AuthTimeout;
use JulioMotol\AuthTimeout\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'AuthTimeout' => AuthTimeout::class
        ];
    }
}
