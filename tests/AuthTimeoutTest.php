<?php

namespace JulioMotol\AuthTimeout\Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Auth\User;
use Illuminate\Session\Store;
use JulioMotol\AuthTimeout\AuthTimeout;
use Mockery\MockInterface;
use function Pest\Laravel\mock as mockInContainer;

it('won\'t initialize when already initialized', function () {
    mockInContainer(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(time());
        $mock->shouldNotReceive('put');
    });

    app(AuthTimeout::class)->init();
});

it('can initialize', function () {
    mockInContainer(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(null);
        $mock->shouldReceive('put')->once();
    });

    app(AuthTimeout::class)->init();
});

it('returns false when no user is logged in', function () {
    mockInContainer(Factory::class, function (MockInterface $mock) {
        $mock->shouldReceive('guard')
            ->once()
            ->andReturn(
                mock(StatefulGuard::class)
                    ->expect(user: fn () => null)
            );
    });

    $result = app(AuthTimeout::class)->check();

    expect($result)->toBeFalse();
});

it('returns false when user is timed out', function ($event) {
    if ($event) {
        config(['auth-timeout.event' => $event]);
    }

    mockInContainer(Factory::class, function (MockInterface $mock) {
        $mock->shouldReceive('guard')
            ->twice()
            ->andReturn(
                mock(StatefulGuard::class)
                    ->expect(
                        user: fn () => new User(),
                        logout: fn () => null
                    )
            );
    });
    mockInContainer(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once();
        $mock->shouldReceive('forget')->once();
    });
    mockInContainer(Dispatcher::class, function (MockInterface $mock) use ($event) {
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
    mockInContainer(Factory::class, function (MockInterface $mock) {
        $mock->shouldReceive('guard')
            ->once()
            ->andReturn(
                mock(StatefulGuard::class)
                    ->expect(user: fn () => new User())
            );
    });
    mockInContainer(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(time());
        $mock->shouldNotReceive('forget');
    });
    mockInContainer(Dispatcher::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('dispatch');
    });

    $result = app(AuthTimeout::class)->check();

    expect($result)->toBeTrue();
});

it('can hit', function () {
    mockInContainer(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('put')->once();
    });

    app(AuthTimeout::class)->hit();
});

it('can get last active at', function () {
    mockInContainer(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn((string) Carbon::now());
    });

    $result = app(AuthTimeout::class)->lastActiveAt();

    expect($result)->toBeInstanceOf(Carbon::class);
});

it('can get last active at (legacy v2)', function () {
    mockInContainer(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(time());
    });

    $result = app(AuthTimeout::class)->lastActiveAt();

    expect($result)->toBeInstanceOf(Carbon::class);
});

it('returns null when getting last active at and not initialized', function () {
    mockInContainer(Store::class, function (MockInterface $mock) {
        $mock->shouldReceive('get')->once()->andReturn(null);
    });

    $result = app(AuthTimeout::class)->lastActiveAt();

    expect($result)->toBeNull();
});

class CustomEvent
{
}
