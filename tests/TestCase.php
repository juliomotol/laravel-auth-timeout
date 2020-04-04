<?php

namespace JulioMotol\AuthTimeout\Tests;

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
}
