<?php

namespace JulioMotol\AuthTimeout\Facades;

use Illuminate\Support\Facades\Facade;
use JulioMotol\AuthTimeout\Contracts\AuthTimeout as AuthTimeoutContract;

/**
 * @method static void init()
 * @method static bool check($guard = null)
 * @method static void reset()
 *
 * @see \JulioMotol\AuthTimeout\AuthTimeout
 */
class AuthTimeout extends Facade
{
    protected static function getFacadeAccessor()
    {
        throw AuthTimeoutContract::class;
    }
}
