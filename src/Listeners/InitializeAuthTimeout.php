<?php

namespace JulioMotol\AuthTimeout\Listeners;

use Illuminate\Support\Facades\Session;
use JulioMotol\AuthTimeout\Facades\AuthTimeout;

class InitializeAuthTimeout
{
    public function handle(): void
    {
        if (Session::isStarted()) {
            AuthTimeout::init();
        }
    }
}
