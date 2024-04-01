<?php

namespace JulioMotol\AuthTimeout\Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Auth\User;
use Illuminate\Session\Store;
use JulioMotol\AuthTimeout\AuthTimeout;
use Mockery;
use Mockery\MockInterface;

use function Pest\Laravel\mock;

it('won\'t initialize when already initialized', function () {
    mock(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(time());
        $mock->shouldNotReceive('put');
    });

    app(AuthTimeout::class)->init();
});

it('can initialize', function () {
    mock(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(null);
        $mock->shouldReceive('put')->once();
    });

    app(AuthTimeout::class)->init();
});

it('returns false when no user is logged in', function () {
    mock(Factory::class, function (MockInterface $mock) {
        $mock->shouldReceive('guard')
            ->once()
            ->andReturnUsing(
                fn () => tap(
                    Mockery::mock(StatefulGuard::class),
                    fn (MockInterface $mock) => $mock->shouldReceive('user')
                        ->once()
                        ->andReturn(null)
                )
            );
    });

    $result = app(AuthTimeout::class)->check();

    expect($result)->toBeFalse();
});

it('returns false when user is timed out', function ($event) {
    if ($event) {
        config(['auth-timeout.event' => $event]);
    }

    mock(Factory::class, function (MockInterface $mock) {
        $mock->shouldReceive('guard')
            ->twice()
            ->andReturnUsing(
                fn () => tap(
                    Mockery::mock(StatefulGuard::class),
                    fn (MockInterface $mock) => $mock->shouldReceive('user')
                        ->andReturn(new User())
                        ->shouldReceive('logout')
                )
            );
    });
    mock(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once();
        $mock->shouldReceive('forget')->once();
    });
    mock(Dispatcher::class, function (MockInterface $mock) use ($event) {
        $mock->shouldReceive('dispatch')->once()->withArgs(function ($disptachedEvent) use ($event) {
            if ($event) {
                return $disptachedEvent::class === $event;
            }

            return $disptachedEvent !== null;
        });
    });

    $result = app(AuthTimeout::class)->check();

    expect($result)->toBeFalse();
})->with([
    'default event' => null,
    'custom event' => CustomEvent::class,
]);

it('returns true when user is not timed out', function () {
    mock(Factory::class, function (MockInterface $mock) {
        $mock->shouldReceive('guard')
            ->once()
            ->andReturnUsing(
                fn () => tap(
                    Mockery::mock(StatefulGuard::class),
                    fn (MockInterface $mock) => $mock->shouldReceive('user')
                        ->once()
                        ->andReturn(new User())
                )
            );
    });
    mock(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(time());
        $mock->shouldNotReceive('forget');
    });
    mock(Dispatcher::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('dispatch');
    });

    $result = app(AuthTimeout::class)->check();

    expect($result)->toBeTrue();
});

it('can hit', function () {
    mock(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('put')->once();
    });

    app(AuthTimeout::class)->hit();
});

it('can get last active at', function () {
    mock(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn((string) Carbon::now());
    });

    $result = app(AuthTimeout::class)->lastActiveAt();

    expect($result)->toBeInstanceOf(Carbon::class);
});

it('can get last active at (legacy v2)', function () {
    mock(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(time());
    });

    $result = app(AuthTimeout::class)->lastActiveAt();

    expect($result)->toBeInstanceOf(Carbon::class);
});

it('returns null when getting last active at and not initialized', function () {
    mock(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(null);
    });

    $result = app(AuthTimeout::class)->lastActiveAt();

    expect($result)->toBeNull();
});

class CustomEvent
{
}
